<?php

namespace App\Jobs;

use App\Events\NewWhatsappMessage;
use App\Models\WhatsappConversation;
use App\Services\GroqAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGroqAiResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 3;

    public function __construct(
        public WhatsappConversation $conversation,
        public string $userMessage,
    ) {}

    public function handle(GroqAiService $groqService): void
    {
        // Build conversation history from recent messages
        $recentMessages = $this->conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take(config('whatsapp.max_history_messages', 50))
            ->get()
            ->reverse();

        $history = [];
        foreach ($recentMessages as $msg) {
            $role = $msg->sender_type === 'customer' ? 'user' : 'assistant';
            $history[] = ['role' => $role, 'content' => $msg->body];
        }

        $aiResponse = $groqService->ask($this->userMessage, $history);

        if (! $aiResponse) {
            Log::warning('Groq AI returned empty response', [
                'conversation_id' => $this->conversation->id,
            ]);
            $aiResponse = 'Maaf, saya tidak dapat memproses permintaan Anda saat ini. Silakan coba lagi nanti.';
        }

        // Store AI response
        $msg = $this->conversation->messages()->create([
            'sender_type' => 'ai',
            'body' => $aiResponse,
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        // Broadcast to dashboard
        broadcast(new NewWhatsappMessage($msg, $this->conversation))->toOthers();

        // Send WhatsApp message via queued job
        SendWhatsappMessageJob::dispatch(
            $this->conversation->whatsapp_phone_number_id,
            $this->conversation->phone,
            $aiResponse
        );

        Log::info('WhatsApp AI response processed', [
            'conversation_id' => $this->conversation->id,
            'phone' => $this->conversation->phone,
        ]);
    }
}
