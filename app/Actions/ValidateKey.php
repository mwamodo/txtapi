<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Requests\SendTextMessageRequest;
use App\Models\ApiKey;
use App\Support\Helpers;
use Illuminate\Http\JsonResponse;

class ValidateKey
{
    use AsAction;

    public function handle(SendTextMessageRequest $request): ApiKey | JsonResponse
    {
        $apiKey = $request->getApiKey();

        if (empty($apiKey)) {
            return Helpers::errorResponse('invalid_key', 'Invalid API Key', 403);
        }

        if (!$apiKey->hasQuotaAvailable()) {
            return Helpers::errorResponse('insufficient_quota', 'Quota Exceeded', 403);
        }

        if (!$apiKey->is_active) {
            return Helpers::errorResponse('inactive_key', 'API Key is inactive', 403);
        }

        return $apiKey;
    }
}
