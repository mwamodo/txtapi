<?php

namespace Database\Factories;

use App\Enums\TextMessageDirection;
use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TextMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone_number_id' => PhoneNumber::factory(),
            'from' => $this->faker->e164PhoneNumber(),
            'to' => $this->faker->e164PhoneNumber(),
            'direction' => TextMessageDirection::OUTBOUND,
            'body' => $this->faker->sentence(),
            'message_status' => 'queued',
        ];
    }
}
