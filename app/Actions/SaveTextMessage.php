<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class SaveTextMessage
{
    use AsAction;

    public function handle(string $phone, string $message)
    {
        // todo: save the text message
    }
}
