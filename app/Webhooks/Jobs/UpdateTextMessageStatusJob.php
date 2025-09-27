<?php

namespace App\Webhooks\Jobs;

use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;
use Illuminate\Support\Facades\Log;
use App\Models\TextMessage;

class UpdateTextMessageStatusJob extends SpatieProcessWebhookJob
{
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;

        $smsSid = $payload['SmsSid'] ?? null;
        $messageStatus = $payload['MessageStatus'] ?? null;

        if (!$smsSid || !$messageStatus) {
            Log::error('UpdateTextMessageStatusJob: Invalid payload', $payload);
            return;
        }

        try {
            $textMessage = TextMessage::where('sid', $smsSid)->first();
            if (!$textMessage) {
                Log::error('UpdateTextMessageStatusJob: Text message not found', [
                    'smsSid' => $smsSid,
                    'payload' => $payload
                ]);
                return;
            }
            $textMessage->update([
                'message_status' => $messageStatus,
            ]);
            // todo: dispatch a message status updated event
        } catch (\Exception $e) {
            Log::error('Error processing SMS status webhook for SID: ' . $smsSid . ' - ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $payload
            ]);
            throw $e;
        }
    }
}
