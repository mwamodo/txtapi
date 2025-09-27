<?php

use App\Enums\BusinessPhoneType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('phone_number');
            $table->string('friendly_name')->nullable();
            $table->string('type')->default(BusinessPhoneType::LOCAL);
            $table->string('phone_number_sid');
            $table->string('caller_id')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }
};
