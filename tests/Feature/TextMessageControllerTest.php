<?php

use App\Enums\TextMessageDirection;
use App\Models\ApiKey;
use App\Models\PhoneNumber;
use App\Models\TextMessage;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Support\Str;

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

    $textMessage = TextMessage::find($response->json('textId'));

    expect($apiKey->fresh()->quota_remaining)->toBe(1);
    expect($textMessage)->not()->toBeNull();
    expect($textMessage->sid)->toBe('SM1234567890');
    expect($textMessage->message_status)->toBe('sent');
});

it('returns validation error details when payload is invalid', function () {
    $response = $this->postJson(route('text.send'), [
        'phone' => '5005550006',
        'message' => 'Test message',
        'key' => 'any-key',
    ]);

    $response
        ->assertStatus(400)
        ->assertExactJson([
            'success' => false,
            'error' => 'invalid_phone',
            'message' => 'Phone number format is invalid',
        ]);
});

it('returns an error when the API key is not found', function () {
    $response = $this->postJson(route('text.send'), [
        'phone' => '+15005550006',
        'message' => 'Test message',
        'key' => 'missing-key',
    ]);

    $response
        ->assertForbidden()
        ->assertExactJson([
            'success' => false,
            'error' => 'invalid_key',
            'message' => 'Invalid API Key',
        ]);
});

it('blocks sending when the API key has no quota remaining', function () {
    $user = User::factory()->create();

    ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 0,
        'is_active' => true,
        'key' => 'txt_noquota',
    ]);

    $response = $this->postJson(route('text.send'), [
        'phone' => '+15005550006',
        'message' => 'Test message',
        'key' => 'txt_noquota',
    ]);

    $response
        ->assertForbidden()
        ->assertExactJson([
            'success' => false,
            'error' => 'insufficient_quota',
            'message' => 'Quota Exceeded',
        ]);
});

it('blocks sending when the API key is inactive', function () {
    $user = User::factory()->create();

    ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 5,
        'is_active' => false,
        'key' => 'txt_inactive',
    ]);

    $response = $this->postJson(route('text.send'), [
        'phone' => '+15005550006',
        'message' => 'Test message',
        'key' => 'txt_inactive',
    ]);

    $response
        ->assertForbidden()
        ->assertExactJson([
            'success' => false,
            'error' => 'inactive_key',
            'message' => 'API Key is inactive',
        ]);
});

it('returns the status for a text message', function () {
    $textMessage = TextMessage::factory()->create([
        'from' => '+15005550006',
        'to' => '+15005550009',
        'direction' => TextMessageDirection::OUTBOUND,
        'message_status' => 'sent',
    ]);

    $response = $this->getJson(route('text.status', ['textId' => $textMessage->getKey()]));

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'status' => 'sent',
            'provider' => 'twilio',
            'errorCode' => null,
        ]);

    expect($response->json('updatedAt'))->toBe($textMessage->fresh()->updated_at?->toISOString());
});

it('returns not found when the text message does not exist', function () {
    $response = $this->getJson(route('text.status', ['textId' => Str::uuid()->toString()]));

    $response
        ->assertNotFound()
        ->assertExactJson([
            'success' => false,
            'error' => 'not_found',
            'message' => 'Text message not found',
        ]);
});
