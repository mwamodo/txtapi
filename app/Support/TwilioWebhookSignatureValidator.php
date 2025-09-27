<?php

namespace App\Support;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class TwilioWebhookSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        // todo: check https://www.twilio.com/docs/usage/webhooks/webhooks-security
        return true;
    }
}
