<?php

use App\Actions\GetTwilioClient;
use App\Models\User;
use Twilio\Rest\Client;

it('creates a twilio client using default credentials', function () {
    config([
        'services.twilio.account_sid' => 'AC1234567890',
        'services.twilio.auth_token' => 'secret-token',
    ]);

    $client = GetTwilioClient::run();

    expect($client)
        ->toBeInstanceOf(Client::class)
        ->and($client->getUsername())->toBe('AC1234567890')
        ->and($client->getPassword())->toBe('secret-token')
        ->and($client->getAccountSid())->toBe('AC1234567890');
});

it('creates a twilio client using user credentials when provided', function () {
    $user = User::factory()->make();
    $user->forceFill([
        'twilio_sid' => 'AC9999999999',
        'token' => 'user-token',
    ]);

    $client = GetTwilioClient::run($user);

    expect($client)
        ->toBeInstanceOf(Client::class)
        ->and($client->getUsername())->toBe('AC9999999999')
        ->and($client->getPassword())->toBe('user-token')
        ->and($client->getAccountSid())->toBe('AC9999999999');
});
