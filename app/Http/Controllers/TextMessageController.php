<?php

namespace App\Http\Controllers;

use App\Actions\SendTextMessage;
use App\Actions\ValidateKey;
use App\Http\Requests\SendTextMessageRequest;
use App\Models\ApiKey;
use App\Models\TextMessage;
use App\Support\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TextMessageController extends Controller
{
    public function send(SendTextMessageRequest $request): JsonResponse
    {
        try {
            $apiKey = ValidateKey::run($request);

            if ($apiKey instanceof JsonResponse) {
                return $apiKey;
            }

            $results = SendTextMessage::run(
                phone: $request->validated()['phone'],
                message: $request->validated()['message'],
                apiKey: $apiKey,
            );

            if (! $results['success']) {
                return Helpers::errorResponse('server_error', $results['error'], 500);
            }

            $apiKey->decrementQuota();
            $results['quotaRemaining'] = $apiKey->quota_remaining;

            return response()->json($results);
        } catch (\Exception $exception) {
            Log::error('SMS sending failed', [
                'error' => $exception->getMessage(),
                'request' => $request->validated(),
            ]);

            return Helpers::errorResponse('server_error', 'Internal server error occurred', 500);
        }
    }

    public function status(string $textId): JsonResponse
    {
        $textMessage = TextMessage::query()->find($textId);

        if (! $textMessage) {
            return Helpers::errorResponse('not_found', 'Text message not found', 404);
        }

        return response()->json([
            'success' => true,
            'status' => $textMessage->message_status ?? 'queued',
            'updatedAt' => $textMessage->updated_at?->toISOString(),
        ]);
    }

    public function quota(string $key): JsonResponse
    {
        $apiKey = ApiKey::query()->firstWhere('key', $key);

        if (! $apiKey) {
            return Helpers::errorResponse('invalid_key', 'Invalid API Key', 403);
        }

        if (! $apiKey->is_active) {
            return Helpers::errorResponse('inactive_key', 'API Key is inactive', 403);
        }

        return response()->json([
            'success' => true,
            'quotaRemaining' => $apiKey->quota_remaining,
        ]);
    }
}
