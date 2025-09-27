<?php

use App\Webhooks\Jobs\UpdateTextMessageStatusJob;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;
use App\Support\TwilioWebhookSignatureValidator;

return [
    'configs' => [
        [
            'name' => 'sms_status',
            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),
            'signature_header_name' => 'Signature',
            'signature_validator' => TwilioWebhookSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_response' => DefaultRespondsTo::class,
            'webhook_model' => WebhookCall::class,
            'store_headers' => [], // todo:add headers and see what is available
            'process_webhook_job' => UpdateTextMessageStatusJob::class,
        ],
    ],
    'delete_after_days' => 30,
    'add_unique_token_to_route_name' => false,
];
