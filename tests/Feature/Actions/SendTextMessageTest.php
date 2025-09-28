<?php

use App\Actions\SendTextMessage;
use App\Models\ApiKey;
use App\Models\PhoneNumber;
use App\Models\TextMessage;
use App\Models\User;
use App\Services\TwilioService;

use function Pest\Laravel\mock;

it('sends a text message and updates the record when delivery succeeds', function () {
    $user = User::factory()->create();

    PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $apiKey = ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 5,
    ]);

    $recipient = fake()->e164PhoneNumber();
    $body = fake()->sentence();

    mock(TwilioService::class)
        ->shouldReceive('sendTextMessage')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'sid' => 'SM1234567890',
                'status' => 'sent',
            ],
        ]);

    $response = SendTextMessage::run($recipient, $body, $apiKey);

    $textMessage = TextMessage::first();

    expect($response)
        ->toMatchArray([
            'success' => true,
            'quotaRemaining' => 5,
            'textId' => $textMessage->getKey(),
        ]);

    expect($textMessage)
        ->not->toBeNull()
        ->and($textMessage->sid)->toBe('SM1234567890')
        ->and($textMessage->message_status)->toBe('sent');
});

it('keeps the text message queued when delivery fails', function () {
    $user = User::factory()->create();

    PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $apiKey = ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 2,
    ]);

    $recipient = fake()->e164PhoneNumber();
    $body = fake()->sentence();

    mock(TwilioService::class)
        ->shouldReceive('sendTextMessage')
        ->once()
        ->andReturn([
            'success' => false,
            'error' => 'Twilio request failed',
        ]);

    $response = SendTextMessage::run($recipient, $body, $apiKey);

    $textMessage = TextMessage::first();

    expect($response)
        ->toMatchArray([
            'success' => false,
            'quotaRemaining' => 2,
            'textId' => $textMessage->getKey(),
        ]);

    expect($textMessage)
        ->not->toBeNull()
        ->and($textMessage->sid)->toBeNull()
        ->and($textMessage->message_status)->toBe('queued');
});
