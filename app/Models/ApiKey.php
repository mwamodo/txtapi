<?php

namespace App\Models;

use App\Traits\HasQuota;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;
    use HasQuota;

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ApiKey $apiKey) {
            if (empty($apiKey->key)) {
                $apiKey->key = self::generateKey();
            }
        });
    }

    public static function generateKey(): string
    {
        return 'txt_'.Str::random(32);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
