<?php

namespace App\Events;

use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewWhatsappMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WhatsappMessage $message,
        public WhatsappConversation $conversation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('whatsapp-dashboard'),
            new Channel('whatsapp-conversation.' . $this->conversation->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->whatsapp_conversation_id,
                'sender_type' => $this->message->sender_type,
                'sender_id' => $this->message->sender_id,
                'body' => e($this->message->body),
                'created_at' => $this->message->created_at->toIso8601String(),
            ],
            'conversation' => [
                'id' => $this->conversation->id,
                'phone' => $this->conversation->phone,
                'customer_name' => $this->conversation->customer_name,
                'mode' => $this->conversation->mode,
            ],
        ];
    }
}
