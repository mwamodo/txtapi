<?php

use App\Enums\TextMessageDirection;
use App\Models\ApiKey;
use App\Models\PhoneNumber;
use App\Models\TextMessage;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Cache;
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

it('accepts the api key from the authorization bearer token header', function () {
    $user = User::factory()->create();

    PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $apiKey = ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 1,
    ]);

    mock(TwilioService::class)
        ->shouldReceive('sendTextMessage')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'sid' => 'SM0987654321',
                'status' => 'queued',
            ],
        ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$apiKey->key)
        ->postJson(route('text.send'), [
            'phone' => '+15005550006',
            'message' => 'Header test message',
        ]);

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quotaRemaining' => 0,
        ]);

    expect($apiKey->fresh()->quota_remaining)->toBe(0);
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
    $user = User::factory()->create();
    $phoneNumber = PhoneNumber::factory()->create(['user_id' => $user->getKey()]);

    $textMessage = TextMessage::factory()->create([
        'user_id' => $user->getKey(),
        'phone_number_id' => $phoneNumber->getKey(),
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

it('returns the remaining quota for an active API key', function () {
    $apiKey = ApiKey::factory()->create([
        'quota_remaining' => 42,
        'is_active' => true,
    ]);

    $response = $this->getJson(route('text.quota', ['key' => $apiKey->key]));

    $response
        ->assertSuccessful()
        ->assertExactJson([
            'success' => true,
            'quotaRemaining' => 42,
        ]);
});

it('returns an error when the API key for quota lookup does not exist', function () {
    $response = $this->getJson(route('text.quota', ['key' => 'missing-key']));

    $response
        ->assertForbidden()
        ->assertExactJson([
            'success' => false,
            'error' => 'invalid_key',
            'message' => 'Invalid API Key',
        ]);
});

it('returns an error when the API key for quota lookup is inactive', function () {
    $apiKey = ApiKey::factory()->create([
        'quota_remaining' => 10,
        'is_active' => false,
    ]);

    $response = $this->getJson(route('text.quota', ['key' => $apiKey->key]));

    $response
        ->assertForbidden()
        ->assertExactJson([
            'success' => false,
            'error' => 'inactive_key',
            'message' => 'API Key is inactive',
        ]);
});

it('returns the same textId when sending with the same idempotency key within 24h', function () {
    $user = User::factory()->create();

    PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $apiKey = ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 5,
    ]);

    mock(TwilioService::class)
        ->shouldReceive('sendTextMessage')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'sid' => 'SM1111111111',
                'status' => 'queued',
            ],
        ]);

    $idempotencyKey = 'test-idempotency-key-'.Str::random(10);

    // First request
    $firstResponse = $this
        ->withHeader('Idempotency-Key', $idempotencyKey)
        ->postJson(route('text.send'), [
            'phone' => '+15005550006',
            'message' => 'Idempotent test message',
            'key' => $apiKey->key,
        ]);

    $firstResponse
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quotaRemaining' => 4,
        ]);

    $firstTextId = $firstResponse->json('textId');

    // Second request with same idempotency key should return the same textId
    $secondResponse = $this
        ->withHeader('Idempotency-Key', $idempotencyKey)
        ->postJson(route('text.send'), [
            'phone' => '+15005550006',
            'message' => 'Idempotent test message',
            'key' => $apiKey->key,
        ]);

    $secondResponse
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'textId' => $firstTextId,
        ]);

    // Quota should not be decremented again
    expect($apiKey->fresh()->quota_remaining)->toBe(4);

    // Only one text message should exist
    expect(TextMessage::count())->toBe(1);
});

it('creates a new text message when idempotency key has expired from cache', function () {
    $user = User::factory()->create();

    PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $apiKey = ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 5,
    ]);

    $idempotencyKey = 'expired-key-'.Str::random(10);

    // Ensure cache is empty (simulating expired cache)
    Cache::forget("idempotency:{$idempotencyKey}");

    mock(TwilioService::class)
        ->shouldReceive('sendTextMessage')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'sid' => 'SM2222222222',
                'status' => 'queued',
            ],
        ]);

    // Request with expired idempotency key should create a new message
    $response = $this
        ->withHeader('Idempotency-Key', $idempotencyKey)
        ->postJson(route('text.send'), [
            'phone' => '+15005550006',
            'message' => 'New message after expiration',
            'key' => $apiKey->key,
        ]);

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quotaRemaining' => 4,
        ]);

    expect(TextMessage::count())->toBe(1);
    expect($apiKey->fresh()->quota_remaining)->toBe(4);
});

it('allows different messages to be sent without idempotency key', function () {
    $user = User::factory()->create();

    PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $apiKey = ApiKey::factory()->create([
        'user_id' => $user->getKey(),
        'quota_remaining' => 5,
    ]);

    mock(TwilioService::class)
        ->shouldReceive('sendTextMessage')
        ->twice()
        ->andReturn([
            'success' => true,
            'data' => [
                'sid' => 'SM3333333333',
                'status' => 'queued',
            ],
        ]);

    // First request without idempotency key
    $firstResponse = $this->postJson(route('text.send'), [
        'phone' => '+15005550006',
        'message' => 'First message',
        'key' => $apiKey->key,
    ]);

    $firstResponse->assertSuccessful();

    // Second request without idempotency key should create a new message
    $secondResponse = $this->postJson(route('text.send'), [
        'phone' => '+15005550006',
        'message' => 'Second message',
        'key' => $apiKey->key,
    ]);

    $secondResponse->assertSuccessful();

    // Two text messages should exist
    expect(TextMessage::count())->toBe(2);
    expect($apiKey->fresh()->quota_remaining)->toBe(3);
    expect($firstResponse->json('textId'))->not()->toBe($secondResponse->json('textId'));
});
