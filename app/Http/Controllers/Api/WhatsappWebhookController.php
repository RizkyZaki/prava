<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    public function __construct(
        private WhatsappService $whatsappService,
    ) {}

    /**
     * Meta webhook verification (GET).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('whatsapp.verify_token')) {
            Log::info('WhatsApp webhook verified');
            return response($challenge, 200);
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook payload (POST).
     */
    public function handle(Request $request): JsonResponse
    {
        // Validate Meta signature
        $signature = $request->header('X-Hub-Signature-256', '');
        $rawPayload = $request->getContent();

        if (config('whatsapp.app_secret') && ! WhatsappService::verifySignature($rawPayload, $signature)) {
            Log::warning('WhatsApp webhook signature validation failed');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->all();

        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return response()->json(['error' => 'Unknown object type'], 404);
        }

        // Cek phone_number_id sebelum logging
        $value = $payload['entry'][0]['changes'][0]['value'] ?? null;
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        if ($phoneNumberId === '863011583558952') {
            Log::info('WhatsApp webhook received', [
                'entry_count' => count($payload['entry'] ?? []),
            ]);
        }

        $this->whatsappService->handleIncomingWebhook($payload);

        return response()->json(['status' => 'ok']);
    }
}
