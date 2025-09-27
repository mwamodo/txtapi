<?php

namespace App\Http\Controllers;

use App\Actions\SendTextMessage;
use App\Http\Requests\SendTextMessageRequest;
use Illuminate\Support\Facades\Log;

class TextMessageController extends Controller
{
    public function send(SendTextMessageRequest $request)
    {
        try {
            $apiKey = $this->validateKey($request);

            $results = SendTextMessage::run(
                phone: $request->validated()['phone'],
                message: $request->validated()['message'],
                apiKey: $apiKey,
            );

            if (!$results['success']) {
                return $this->errorResponse('server_error', $results['error'], 500);
            }

            return response()->json($results);
        } catch (\Exception $exception) {
            Log::error('SMS sending failed', [
                'error' => $exception->getMessage(),
                'request' => $request->validated(),
            ]);

            return $this->errorResponse('server_error', 'Internal server error occurred', 500);
        }
    }

    private function validateKey(SendTextMessageRequest $request)
    {
        $apiKey = $request->getApiKey();

        if (empty($apiKey)) {
            return $this->errorResponse('invalid_key', 'Invalid API Key', 402);
        }

        if (!$apiKey->hasQuotaAvailable()) {
            return $this->errorResponse('insufficient_quota', 'Quota Exceeded', 402);
        }

        return $apiKey;
    }

    private function errorResponse(string $error, string $message, int $status)
    {
        return response()->json([
            'success' => false,
            'error' => $error,
            'message' => $message,
        ], $status);
    }
}
