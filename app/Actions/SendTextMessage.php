<?php

namespace App\Actions;

use App\Models\ApiKey;
use App\Models\TextMessage;
use App\Services\TwilioService;
use Lorisleiva\Actions\Concerns\AsAction;

final class SendTextMessage
{
    use AsAction;

    public function __construct(private TwilioService $clientService) {}

    public function handle(string $phone, string $message, ApiKey $apiKey): array
    {
        $textMessage = SaveTextMessage::run($phone, $message);

        $smsResults = $this->clientService->sendTextMessage($textMessage);

        $this->updateTextMessage($textMessage, $smsResults);

        $response = [
            'success' => $smsResults['success'],
            'quotaRemaining' => $apiKey->quota_remaining,
            'textId' => $textMessage->id,
        ];

        return $response;
    }

    private function updateTextMessage(TextMessage $textMessage, array $smsResults)
    {
        if ($smsResults['success'] && !empty($smsResults['data']['sid'])) {
            $textMessage->update([
                'sid' => $smsResults['data']['sid'],
                'message_status' => $smsResults['data']['status'],
            ]);
        }
    }
}
