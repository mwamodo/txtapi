<?php

use App\Actions\ValidateKey;
use App\Http\Requests\SendTextMessageRequest;
use App\Models\ApiKey;
use Illuminate\Http\JsonResponse;

use function Pest\Laravel\mock;

it('returns an invalid key error response when the api key is missing', function () {
    $request = mock(SendTextMessageRequest::class);
    $request->shouldReceive('getApiKey')->once()->andReturnNull();

    $response = ValidateKey::run($request);

    expect($response)
        ->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(403)
        ->and($response->getData(true))->toMatchArray([
            'success' => false,
            'error' => 'invalid_key',
            'message' => 'Invalid API Key',
        ]);
});

it('returns an insufficient quota error response when quota has been exhausted', function () {
    $apiKey = ApiKey::factory()->create([
        'quota_remaining' => 0,
    ]);

    $request = mock(SendTextMessageRequest::class);
    $request->shouldReceive('getApiKey')->once()->andReturn($apiKey);

    $response = ValidateKey::run($request);

    expect($response)
        ->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(403)
        ->and($response->getData(true))->toMatchArray([
            'success' => false,
            'error' => 'insufficient_quota',
            'message' => 'Quota Exceeded',
        ]);
});

it('returns an inactive key error response when the key is disabled', function () {
    $apiKey = ApiKey::factory()->create([
        'is_active' => false,
        'quota_remaining' => 5,
    ]);

    $request = mock(SendTextMessageRequest::class);
    $request->shouldReceive('getApiKey')->once()->andReturn($apiKey);

    $response = ValidateKey::run($request);

    expect($response)
        ->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(403)
        ->and($response->getData(true))->toMatchArray([
            'success' => false,
            'error' => 'inactive_key',
            'message' => 'API Key is inactive',
        ]);
});

it('returns the api key when it is valid', function () {
    $apiKey = ApiKey::factory()->create([
        'quota_remaining' => 3,
        'is_active' => true,
    ]);

    $request = mock(SendTextMessageRequest::class);
    $request->shouldReceive('getApiKey')->once()->andReturn($apiKey);

    $result = ValidateKey::run($request);

    expect($result)
        ->toBeInstanceOf(ApiKey::class)
        ->and($result->is($apiKey))->toBeTrue();
});
