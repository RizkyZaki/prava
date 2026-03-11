<?php

namespace App\Events;

use App\Models\WhatsappConversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WhatsappConversation $conversation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('whatsapp-dashboard'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => [
                'id' => $this->conversation->id,
                'phone' => $this->conversation->phone,
                'customer_name' => $this->conversation->customer_name,
                'mode' => $this->conversation->mode,
                'assigned_to' => $this->conversation->assigned_to,
                'ended_at' => $this->conversation->ended_at?->toIso8601String(),
                'last_message_at' => $this->conversation->last_message_at?->toIso8601String(),
            ],
        ];
    }
}
