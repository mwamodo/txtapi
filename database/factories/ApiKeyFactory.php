<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApiKeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'quota_remaining' => 100,
        ];
    }
}
