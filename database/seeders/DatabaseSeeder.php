<?php

namespace Database\Seeders;

use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Rick Mwamodo',
            'email' => 'erick@mwamodo.com',
        ]);

        PhoneNumber::factory()->create([ 'user_id' => $user->id ]);
    }
}
