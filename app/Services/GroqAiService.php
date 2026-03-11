<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqAiService
{
    /**
     * Send a message to Groq AI and get a response.
     *
     * @param  array<int, array{role: string, content: string}>  $conversationHistory
     */
    public function ask(string $userMessage, array $conversationHistory = []): ?string
    {
        $apiKey = config('whatsapp.groq_api_key');
        if (! $apiKey) {
            Log::error('Groq API key not configured');
            return null;
        }

        $messages = [
            ['role' => 'system', 'content' => config('whatsapp.groq_system_prompt')],
        ];

        // Append recent conversation history for context
        foreach (array_slice($conversationHistory, -10) as $entry) {
            $messages[] = $entry;
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('whatsapp.groq_model'),
                    'messages' => $messages,
                    'max_tokens' => 1024,
                ]);

            if ($response->failed()) {
                Log::error('Groq API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('choices.0.message.content');
        } catch (\Throwable $e) {
            Log::error('Groq API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
