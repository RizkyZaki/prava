<?php

namespace App\Filament\Pages;

use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\WhatsappService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class WhatsappDashboard extends Page
{
    use HasPageShield;
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string|\UnitEnum|null $navigationGroup = 'Customer Service';
    protected static ?string $navigationLabel = 'WhatsApp CS';
    protected static ?string $title = 'WhatsApp Customer Service';
    protected static ?int $navigationSort = 1;
    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.pages.whatsapp-dashboard';

    public bool $fullWidth = true;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public ?int $selectedConversationId = null;
    public string $replyMessage = '';
    public string $search = '';
    public $mediaFile = null;

    public function mount(): void
    {
        // No initialization needed — conversations and messages are computed fresh each render
    }

    #[Computed]
    public function conversations(): Collection
    {
        $query = WhatsappConversation::active()
            ->with(['assignedAdmin', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount('messages')
            ->orderByDesc('last_message_at');

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

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        unset($this->conversations, $this->messages, $this->selectedConversation);
        $this->dispatch('scroll-to-bottom');
    }

    public function updatedSearch(): void
    {
        unset($this->conversations);
    }

    #[Computed]
    public function selectedConversation(): ?WhatsappConversation
    {
        if (! $this->selectedConversationId) {
            return null;
        }

        return WhatsappConversation::find($this->selectedConversationId);
    }

    public function sendReply(): void
    {
        $text = trim($this->replyMessage);
        if ($text === '' || ! $this->selectedConversationId) {
            return;
        }

        $conversation = WhatsappConversation::find($this->selectedConversationId);
        if (! $conversation || $conversation->ended_at) {
            $this->dispatch('notify', type: 'warning', message: 'Conversation sudah berakhir.');
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

        $this->selectedConversationId = null;
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

        $this->selectedConversationId = null;
        unset($this->conversations, $this->messages, $this->selectedConversation);
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

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }
}
