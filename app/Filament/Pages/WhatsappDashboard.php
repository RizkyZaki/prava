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

class WhatsappDashboard extends Page
{
    use HasPageShield;

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
    public Collection $conversations;
    public Collection $messages;

    public function mount(): void
    {
        $this->conversations = collect();
        $this->messages = collect();
        $this->loadConversations();
    }

    public function loadConversations(): void
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

        $this->conversations = $query->get();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->loadMessages();
        $this->dispatch('scroll-to-bottom');
    }

    public function updatedSearch(): void
    {
        $this->loadConversations();
    }

    public function loadMessages(): void
    {
        if (! $this->selectedConversationId) {
            $this->messages = collect();
            return;
        }

        $this->messages = WhatsappMessage::where('whatsapp_conversation_id', $this->selectedConversationId)
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
        $this->loadMessages();
        $this->loadConversations();
        $this->dispatch('scroll-to-bottom');
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
        $this->messages = collect();
        $this->loadConversations();
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
        $this->messages = collect();
        $this->loadConversations();
    }

    /**
     * Polling: refresh conversations list every 5 seconds.
     */
    public function poll(): void
    {
        $this->loadConversations();

        if ($this->selectedConversationId) {
            $this->loadMessages();
        }
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }
}
