<?php

namespace Database\Factories;

use App\Enums\BusinessPhoneType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhoneNumberFactory extends Factory
{
    public function definition(): array
    {
        $number = fake()->unique()->e164PhoneNumber();

        return [
            'name' => fake()->word(),
            'phone_number' => $number,
            'friendly_name' => preg_replace(
                '/^\+1(\d{3})(\d{3})(\d{4})$/',
                '($1) $2-$3',
                $number
            ),
            'phone_number_sid' => fake()->unique()->uuid(),
        ];
    }
}
