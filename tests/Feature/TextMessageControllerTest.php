<?php

use App\Models\ApiKey;
use App\Models\PhoneNumber;
use App\Models\User;
use App\Services\TwilioService;

use function Pest\Laravel\mock;

it('decrements quota after sending a text message', function () {
    $user = User::factory()->create();

    PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $apiKey = ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 2,
    ]);

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

    $response = $this->postJson(route('text.send'), [
        'phone' => '+15005550006',
        'message' => 'Test message',
        'key' => $apiKey->key,
    ]);

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quotaRemaining' => 1,
        ]);

    expect($apiKey->fresh()->quota_remaining)->toBe(1);
});
