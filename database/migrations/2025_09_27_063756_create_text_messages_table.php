<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('text_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('phone_number_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('from');
            $table->string('to');
            $table->string('sid', 500)->nullable();
            $table->string('direction');
            $table->text('body')->nullable();
            $table->string('message_status')->nullable();
            $table->timestamps();
        });
    }
};
