<?php

namespace App\Actions;

use App\Models\ApiKey;
use App\Services\TwilioService;
use Lorisleiva\Actions\Concerns\AsAction;

final class SendTextMessage
{
    use AsAction;

    public function __construct(private TwilioService $clientService) {}

    public function handle(string $phone, string $message, ApiKey $apiKey)
    {
        $smsResults = $this->clientService->sendTextMessage($phone, $message);

        $response = [...$smsResults];

        return $response;
    }
}
