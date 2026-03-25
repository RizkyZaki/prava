<?php

namespace App\Livewire;

use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\WhatsappService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.whatsapp')]

class WhatsappChat extends Component
{
    use WithFileUploads;

    public ?int $selectedConversationId = null;
    public string $replyMessage = '';
    public string $search = '';
    public $mediaFile = null;
    public bool $showHistory = false;
    public bool $showEndChatModal = false;
    public bool $showMobileSidebar = true;

    // Tambahan: filter nomor WhatsApp
    public string $selectedPhoneNumberId = '863011583558952';

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasRole('super_admin'), 403);
    }

    public function updatedSelectedPhoneNumberId()
    {
        $this->selectedConversationId = null;
        unset($this->conversations);
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
        unset($this->conversations);
    }

    public function confirmEndChat(): void
    {
        $this->showEndChatModal = true;
    }

    public function cancelEndChat(): void
    {
        $this->showEndChatModal = false;
    }

    #[Computed]
    public function conversations(): Collection
    {
        $query = WhatsappConversation::query()
            ->with(['assignedAdmin', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount('messages')
            ->orderByDesc('last_message_at');

        if ($this->showHistory) {
            $query->whereNotNull('ended_at');
        } else {
            $query->whereNull('ended_at');
        }

        // Filter berdasarkan nomor WhatsApp yang dipilih
        if ($this->selectedPhoneNumberId) {
            $query->where('whatsapp_phone_number_id', $this->selectedPhoneNumberId);
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    #[Computed]
    public function messages(): Collection
    {
        if (! $this->selectedConversationId) {
            return collect();
        }

        return WhatsappMessage::where('whatsapp_conversation_id', $this->selectedConversationId)
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function selectedConversation(): ?WhatsappConversation
    {
        if (! $this->selectedConversationId) {
            return null;
        }

        return WhatsappConversation::find($this->selectedConversationId);
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->showMobileSidebar = false;
        unset($this->conversations, $this->messages, $this->selectedConversation);
        $this->dispatch('scroll-to-bottom');
    }

    public function backToList(): void
    {
        $this->showMobileSidebar = true;
    }

    public function updatedSearch(): void
    {
        unset($this->conversations);
    }

    public function sendReply(): void
    {
        $text = trim($this->replyMessage);
        if ($text === '' || ! $this->selectedConversationId) {
            return;
        }

        $conversation = WhatsappConversation::find($this->selectedConversationId);
        if (! $conversation || $conversation->ended_at) {
            return;
        }

        $service = app(WhatsappService::class);
        $service->sendAdminReply($conversation, $text, Auth::id());

        $this->replyMessage = '';
        unset($this->conversations, $this->messages, $this->selectedConversation);
        $this->dispatch('scroll-to-bottom');
    }

    public function sendMedia(): void
    {
        if (! $this->mediaFile || ! $this->selectedConversationId) {
            return;
        }

        $conversation = WhatsappConversation::find($this->selectedConversationId);
        if (! $conversation || $conversation->ended_at) {
            return;
        }

        $mime = $this->mediaFile->getMimeType();
        $mediaType = str_starts_with($mime, 'image/') ? 'image'
            : (str_starts_with($mime, 'video/') ? 'video'
            : (str_starts_with($mime, 'audio/') ? 'audio' : 'document'));

        $path = $this->mediaFile->store('whatsapp-media', 'public');
        $mediaUrl = url('storage/' . $path);

        $caption = trim($this->replyMessage) !== '' ? trim($this->replyMessage) : null;

        $service = app(WhatsappService::class);
        $service->sendAdminMedia($conversation, $mediaUrl, $mediaType, $caption, Auth::id());

        $this->mediaFile = null;
        $this->replyMessage = '';
        unset($this->conversations, $this->messages, $this->selectedConversation);
        $this->dispatch('scroll-to-bottom');
    }

    public function removeMedia(): void
    {
        $this->mediaFile = null;
    }

    public function endChat(): void
    {
        if (! $this->selectedConversationId) {
            return;
        }

        $conversation = WhatsappConversation::find($this->selectedConversationId);
        if (! $conversation) {
            return;
        }

        $service = app(WhatsappService::class);
        $service->endChat($conversation);

        $this->showEndChatModal = false;
        $this->selectedConversationId = null;
        $this->showMobileSidebar = true;
        unset($this->conversations, $this->messages, $this->selectedConversation);
    }

    public function switchToAi(): void
    {
        if (! $this->selectedConversationId) {
            return;
        }

        $conversation = WhatsappConversation::find($this->selectedConversationId);
        if (! $conversation) {
            return;
        }

        $service = app(WhatsappService::class);
        $service->switchToAi($conversation);

        unset($this->conversations, $this->selectedConversation);
    }

    /**
     * Called by Echo when a new WhatsApp message is broadcast.
     */
    public function onNewMessage(array $data = []): void
    {
        unset($this->conversations);

        $conversationId = $data['message']['conversation_id'] ?? null;
        if ($conversationId && $conversationId == $this->selectedConversationId) {
            unset($this->messages, $this->selectedConversation);
            $this->dispatch('scroll-to-bottom');
        }
    }

    /**
     * Called by Echo when a conversation is updated.
     */
    public function onConversationUpdated(array $data = []): void
    {
        unset($this->conversations);

        $conversationId = $data['conversation']['id'] ?? null;
        if ($conversationId && $conversationId == $this->selectedConversationId) {
            unset($this->selectedConversation);
        }
    }

    public function render()
    {
        return view('livewire.whatsapp-chat');
    }
}
