<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsappMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public string $phoneNumberId,
        public string $to,
        public string $mediaType,
        public string $mediaUrl,
        public ?string $caption = null,
    ) {}

    public function handle(): void
    {
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.api_base_url');

        $url = "{$baseUrl}/{$apiVersion}/{$this->phoneNumberId}/messages";

        $mediaPayload = [
            'link' => $this->mediaUrl,
        ];

        if ($this->caption) {
            $mediaPayload['caption'] = $this->caption;
        }

        $response = Http::withToken($accessToken)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $this->to,
                'type' => $this->mediaType,
                $this->mediaType => $mediaPayload,
            ]);

        if ($response->failed()) {
            Log::error('Failed to send WhatsApp media', [
                'to' => $this->to,
                'media_type' => $this->mediaType,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            $this->fail(new \RuntimeException('Meta WhatsApp API returned ' . $response->status()));
            return;
        }

        Log::info('WhatsApp media sent', [
            'to' => $this->to,
            'media_type' => $this->mediaType,
            'phone_number_id' => $this->phoneNumberId,
        ]);
    }
}
