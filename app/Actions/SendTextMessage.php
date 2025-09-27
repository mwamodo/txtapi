<?php

namespace App\Actions;

use App\Models\ApiKey;
use Lorisleiva\Actions\Concerns\AsAction;

final class SendTextMessage
{
    use AsAction;

    public function __construct()
    {
        // todo: inject smsservice provider
    }

    public function handle(string $phone, string $message, ApiKey $apiKey)
    {
        // todo: send the message
        return [];
    }
}
