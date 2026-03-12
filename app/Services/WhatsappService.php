<?php

namespace App\Services;

use App\Events\ConversationUpdated;
use App\Events\NewWhatsappMessage;
use App\Jobs\SendWhatsappMediaJob;
use App\Jobs\SendWhatsappMessageJob;
use App\Jobs\SendWhatsappInteractiveJob;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Handle incoming webhook payload from Meta.
     */
    public function handleIncomingWebhook(array $payload): void
    {
        $value = $payload['entry'][0]['changes'][0]['value'] ?? null;
        if (! $value) {
            return;
        }

        $contact = $value['contacts'][0] ?? null;
        $message = $value['messages'][0] ?? null;
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        // Filter: hanya proses pesan untuk nomor perusahaan
        if ($phoneNumberId !== '863011583558952') {
            return;
        }

        if (! $message || ! $phoneNumberId) {
            return;
        }

        $from = $message['from'];
        $customerName = $contact['profile']['name'] ?? null;

        $conversation = $this->findOrCreateConversation($from, $phoneNumberId, $customerName);

        if ($message['type'] === 'interactive') {
            $this->handleInteractiveReply($conversation, $message);
            return;
        }

        if (in_array($message['type'], ['image', 'video', 'audio', 'document'])) {
            $this->handleMediaMessage($conversation, $message);
            return;
        }

        if ($message['type'] === 'text') {
            $this->handleTextMessage($conversation, $message);
        }
    }

    /**
     * Find or create an active conversation for a phone number.
     */
    protected function findOrCreateConversation(string $phone, string $phoneNumberId, ?string $customerName): WhatsappConversation
    {
        $conversation = WhatsappConversation::where('phone', $phone)
            ->whereNull('ended_at')
            ->first();

        if ($conversation) {
            if ($customerName && $conversation->customer_name !== $customerName) {
                $conversation->update(['customer_name' => $customerName]);
            }
            return $conversation;
        }

        return WhatsappConversation::create([
            'phone' => $phone,
            'customer_name' => $customerName,
            'whatsapp_phone_number_id' => $phoneNumberId,
            'mode' => 'selection',
        ]);
    }

    /**
     * Handle interactive button reply (AI or Admin selection).
     */
    protected function handleInteractiveReply(WhatsappConversation $conversation, array $message): void
    {
        $selection = $message['interactive']['button_reply']['id'] ?? null;

        if ($selection === 'select_ai') {
            $conversation->update(['mode' => 'ai']);
            broadcast(new ConversationUpdated($conversation->fresh()))->toOthers();

            SendWhatsappMessageJob::dispatch(
                $conversation->whatsapp_phone_number_id,
                $conversation->phone,
                '✅ Mode AI Aktif. Silakan kirim pertanyaan Anda!'
            );
        } elseif ($selection === 'select_admin') {
            $conversation->update(['mode' => 'admin']);
            broadcast(new ConversationUpdated($conversation->fresh()))->toOthers();

            SendWhatsappMessageJob::dispatch(
                $conversation->whatsapp_phone_number_id,
                $conversation->phone,
                '👨‍💻 Mode Admin Aktif. AI dinonaktifkan. Mohon tunggu balasan dari tim kami.'
            );
        }
    }

    /**
     * Handle incoming media message (image, video, audio, document).
     */
    protected function handleMediaMessage(WhatsappConversation $conversation, array $message): void
    {

        $type = $message['type'];
        $mediaData = $message[$type] ?? [];
        $mediaId = $mediaData['id'] ?? null;
        $caption = $mediaData['caption'] ?? null;
        $mime = $mediaData['mime_type'] ?? null;
        $waMessageId = $message['id'] ?? null;

        // Gunakan URL proxy backend jika mediaId ada
        $mediaUrl = $mediaId ? url("/api/whatsapp/media/{$mediaId}") : null;

        $msg = $conversation->messages()->create([
            'sender_type' => 'customer',
            'body' => $caption ?? "[{$type}]",
            'media_type' => $type,
            'media_url' => $mediaUrl,
            'media_id' => $mediaId,
            'media_mime' => $mime,
            'whatsapp_message_id' => $waMessageId,
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new NewWhatsappMessage($msg, $conversation))->toOthers();

        // If mode is selection, send selection buttons
        if ($conversation->mode === 'selection') {
            SendWhatsappInteractiveJob::dispatch(
                $conversation->whatsapp_phone_number_id,
                $conversation->phone
            );
        }
    }

    /**
     * Get media URL from Meta using media ID.
     */
    protected function getMediaUrl(string $mediaId): ?string
    {
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.api_base_url');

        $response = Http::withToken($accessToken)
            ->get("{$baseUrl}/{$apiVersion}/{$mediaId}");

        if ($response->successful()) {
            return $response->json('url');
        }

        Log::warning('Failed to get media URL', [
            'media_id' => $mediaId,
            'status' => $response->status(),
        ]);

        return null;
    }

    /**
     * Send media from admin dashboard.
     */
    public function sendAdminMedia(WhatsappConversation $conversation, string $mediaUrl, string $mediaType, ?string $caption, int $adminUserId): WhatsappMessage
    {
        $msg = $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $adminUserId,
            'body' => $caption ?? "[{$mediaType}]",
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'assigned_to' => $conversation->assigned_to ?? $adminUserId,
        ]);

        SendWhatsappMediaJob::dispatch(
            $conversation->whatsapp_phone_number_id,
            $conversation->phone,
            $mediaType,
            $mediaUrl,
            $caption
        );

        broadcast(new NewWhatsappMessage($msg, $conversation->fresh()))->toOthers();

        return $msg;
    }

    /**
     * Handle plain text message.
     */
    protected function handleTextMessage(WhatsappConversation $conversation, array $message): void
    {
        $text = $message['text']['body'] ?? '';
        $waMessageId = $message['id'] ?? null;
        $textLower = mb_strtolower($text);

        // Store customer message
        $msg = $conversation->messages()->create([
            'sender_type' => 'customer',
            'body' => $text,
            'whatsapp_message_id' => $waMessageId,
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new NewWhatsappMessage($msg, $conversation))->toOthers();

        // Keyword-based mode switching
        if ($textLower === 'pilih ai') {
            $conversation->update(['mode' => 'ai']);
            broadcast(new ConversationUpdated($conversation->fresh()))->toOthers();
            SendWhatsappMessageJob::dispatch(
                $conversation->whatsapp_phone_number_id,
                $conversation->phone,
                '✅ Berhasil kembali ke Mode AI.'
            );
            return;
        }

        if (in_array($textLower, ['pilih admin', 'panggil cs', 'halo admin'])) {
            $conversation->update(['mode' => 'admin']);
            broadcast(new ConversationUpdated($conversation->fresh()))->toOthers();
            SendWhatsappMessageJob::dispatch(
                $conversation->whatsapp_phone_number_id,
                $conversation->phone,
                '👨‍💻 Mode Admin Aktif. AI telah dimatikan, silakan tunggu balasan dari tim kami.'
            );
            return;
        }

        // Route based on current mode
        if ($conversation->mode === 'admin') {
            // Do nothing — admin will reply from dashboard
            return;
        }

        if ($conversation->mode === 'ai') {
            // Dispatch AI response job
            \App\Jobs\ProcessGroqAiResponseJob::dispatch($conversation, $text);
            return;
        }

        // Mode = 'selection' — send selection buttons
        SendWhatsappInteractiveJob::dispatch(
            $conversation->whatsapp_phone_number_id,
            $conversation->phone
        );
    }

    /**
     * Send a reply from admin and store in messages.
     */
    public function sendAdminReply(WhatsappConversation $conversation, string $text, int $adminUserId): WhatsappMessage
    {
        $msg = $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $adminUserId,
            'body' => $text,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'assigned_to' => $conversation->assigned_to ?? $adminUserId,
        ]);

        SendWhatsappMessageJob::dispatch(
            $conversation->whatsapp_phone_number_id,
            $conversation->phone,
            $text
        );

        broadcast(new NewWhatsappMessage($msg, $conversation->fresh()))->toOthers();

        Log::info('WhatsApp admin reply sent', [
            'conversation_id' => $conversation->id,
            'admin_id' => $adminUserId,
            'phone' => $conversation->phone,
        ]);

        return $msg;
    }

    /**
     * End an admin chat session, switch back to AI.
     */
    public function endChat(WhatsappConversation $conversation): void
    {
        $closingMessage = 'Terima kasih. Admin telah mengakhiri sesi chat ini. Sekarang Anda terhubung kembali dengan AI.';

        $conversation->messages()->create([
            'sender_type' => 'system',
            'body' => $closingMessage,
        ]);

        SendWhatsappMessageJob::dispatch(
            $conversation->whatsapp_phone_number_id,
            $conversation->phone,
            $closingMessage
        );

        $conversation->update([
            'mode' => 'ai',
            'ended_at' => now(),
            'last_message_at' => now(),
        ]);

        broadcast(new ConversationUpdated($conversation->fresh()))->toOthers();

        Log::info('WhatsApp chat ended', [
            'conversation_id' => $conversation->id,
            'phone' => $conversation->phone,
        ]);
    }

    /**
     * Switch conversation back to AI mode (without ending).
     */
    public function switchToAi(WhatsappConversation $conversation): void
    {
        $conversation->update(['mode' => 'ai']);

        broadcast(new ConversationUpdated($conversation->fresh()))->toOthers();

        Log::info('WhatsApp conversation switched to AI', [
            'conversation_id' => $conversation->id,
            'phone' => $conversation->phone,
        ]);
    }

    /**
     * Validate Meta webhook signature.
     */
    public static function verifySignature(string $payload, string $signature): bool
    {
        $appSecret = config('whatsapp.app_secret');
        if (! $appSecret) {
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expectedSignature, $signature);
    }
}
