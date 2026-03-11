<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsappInteractiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public string $phoneNumberId,
        public string $to,
    ) {}

    public function handle(): void
    {
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.api_base_url');

        $url = "{$baseUrl}/{$apiVersion}/{$this->phoneNumberId}/messages";

        $response = Http::withToken($accessToken)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $this->to,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => 'Selamat datang di PST AI. Silakan pilih metode bantuan:',
                    ],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => ['id' => 'select_ai', 'title' => '🤖 Tanya AI'],
                            ],
                            [
                                'type' => 'reply',
                                'reply' => ['id' => 'select_admin', 'title' => '👨‍💻 Bicara Admin'],
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('Failed to send WhatsApp interactive message', [
                'to' => $this->to,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            $this->fail(new \RuntimeException('Meta WhatsApp API returned ' . $response->status()));
            return;
        }

        Log::info('WhatsApp interactive message sent', [
            'to' => $this->to,
            'phone_number_id' => $this->phoneNumberId,
        ]);
    }
}
