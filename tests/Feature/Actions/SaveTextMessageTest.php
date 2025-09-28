<?php

use App\Actions\SaveTextMessage;
use App\Enums\TextMessageDirection;
use App\Models\PhoneNumber;
use App\Models\TextMessage;
use App\Models\User;

it('saves an outbound text message', function () {
    $user = User::factory()->create();
    $sendingNumber = PhoneNumber::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => true,
    ]);

    $recipient = fake()->e164PhoneNumber();
    $body = fake()->sentence();

    $textMessage = SaveTextMessage::run($recipient, $body);

    expect($textMessage)->toBeInstanceOf(TextMessage::class)
        ->and($textMessage->phone_number_id)->toBe($sendingNumber->getKey())
        ->and($textMessage->user_id)->toBe($sendingNumber->user_id)
        ->and($textMessage->from)->toBe($sendingNumber->phone_number)
        ->and($textMessage->to)->toBe($recipient)
        ->and($textMessage->direction)->toBe(TextMessageDirection::OUTBOUND)
        ->and($textMessage->body)->toBe($body)
        ->and($textMessage->message_status)->toBe('queued');
});
