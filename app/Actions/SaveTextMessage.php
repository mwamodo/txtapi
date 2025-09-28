<?php

namespace App\Actions;

use App\Enums\TextMessageDirection;
use App\Models\PhoneNumber;
use App\Models\TextMessage;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

class SaveTextMessage
{
    use AsAction;

    public function handle(string $phone, string $message): TextMessage
    {
        $sendingPhoneNumber = PhoneNumber::query()
            ->where('is_primary', true)
            ->first()
            ?? PhoneNumber::query()->first();

        if (! $sendingPhoneNumber) {
            throw new RuntimeException('No sending phone number configured.');
        }

        return TextMessage::create([
            'user_id' => $sendingPhoneNumber->user_id,
            'phone_number_id' => $sendingPhoneNumber->getKey(),
            'from' => $sendingPhoneNumber->phone_number,
            'to' => $phone,
            'direction' => TextMessageDirection::OUTBOUND,
            'body' => $message,
            'message_status' => 'queued',
        ]);
    }
}
