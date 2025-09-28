<?php

namespace App\Services;

use App\Models\PhoneNumber;
use App\Models\TextMessage;
use Twilio\Rest\Client;
use Exception;

class TwilioService
{
    public function __construct(
        private Client $twilioClient
    ){}

    public function sendTextMessage(TextMessage $textMessage)
    {
        try {
            $parameters = [
                'from' => $textMessage->from,
                'body' => $textMessage->body,
                'statusCallback' => app()->environment('local') ?
                    config('services.twilio.local_sms_status') :
                    url('/') . '/api/external/sms/status',
            ];

            $response = $this->twilioClient->messages->create($textMessage->to, $parameters);

            $result['data'] = $response->toArray();

            if (! empty($result['data']['errorCode'])) {
                throw new Exception('Send SMS request failed: ' . $result['data']['errorCode']);
            }

            $result['success'] = true;

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
