<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WhatsappMediaController extends Controller
{
    /**
     * Proxy WhatsApp media to the client with authentication.
     */
    public function show(Request $request, $mediaId)
    {
        $accessToken = config('whatsapp.access_token');
        $apiVersion = config('whatsapp.api_version');
        $baseUrl = config('whatsapp.api_base_url');

        // Get media URL from WhatsApp
        $mediaResponse = Http::withToken($accessToken)
            ->get("{$baseUrl}/{$apiVersion}/{$mediaId}");

        if (!$mediaResponse->successful()) {
            Log::warning('Failed to get WhatsApp media URL', [
                'media_id' => $mediaId,
                'status' => $mediaResponse->status(),
                'body' => $mediaResponse->body(),
            ]);
            return response()->json(['error' => 'Failed to get media URL'], 400);
        }

        $mediaUrl = $mediaResponse->json('url');
        if (!$mediaUrl) {
            return response()->json(['error' => 'Media URL not found'], 404);
        }

        // Download media file from WhatsApp
        $fileResponse = Http::withToken($accessToken)->withHeaders([
            'Accept' => '*/*',
        ])->get($mediaUrl);

        if (!$fileResponse->successful()) {
            Log::warning('Failed to download WhatsApp media', [
                'media_id' => $mediaId,
                'status' => $fileResponse->status(),
                'body' => $fileResponse->body(),
            ]);
            return response()->json(['error' => 'Failed to download media'], 400);
        }

        $contentType = $fileResponse->header('Content-Type', 'application/octet-stream');
        $filename = $mediaId;

        return response($fileResponse->body(), 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
