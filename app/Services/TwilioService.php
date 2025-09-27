<?php

namespace App\Services;

use App\Models\PhoneNumber;
use Twilio\Rest\Client;
use Exception;

class TwilioService
{
    public function __construct(
        private Client $twilioClient
    ){}

    public function sendTextMessage(string $phone, string $message)
    {
        try {
            $parameters = [
                'from' => $this->getSendingPhoneNumber()->phone_number,
                'body' => $message,
                'statusCallback' => app()->environment('local') ?
                    config('services.twilio.local_sms_status') :
                    url('/') . '/api/external/sms/status',
            ];

            $response = $this->twilioClient->messages->create($phone, $parameters);

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

    private function getSendingPhoneNumber(): PhoneNumber
    {
        // todo: implement the logic to get the sending phone number
        return PhoneNumber::first();
    }
}
