<?php

namespace App\Actions\Twilio;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;

class GetTwilioClient
{
    use AsAction;

    /**
     * @throws ConfigurationException
     */
    public function handle(?User $user = null): Client
    {
        return new Client(
            $user ? $user->twilio_sid : config('services.twilio.account_sid'),
            $user ? $user->token : config('services.twilio.auth_token')
        );
    }
}
