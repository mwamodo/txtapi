<?php

namespace App\Actions;

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
        // future: user can be a twilio sub-account
        // then we can pass sid & token saved in user table
        return new Client(
            $user ? $user->twilio_sid : config('services.twilio.account_sid'),
            $user ? $user->token : config('services.twilio.auth_token')
        );
    }
}
