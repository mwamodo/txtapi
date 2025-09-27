<?php

namespace App\Models;

use App\Enums\BusinessPhoneType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhoneNumber extends Model
{
    use HasFactory;
    use HasUuids;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'type' => BusinessPhoneType::class,
            'settings' => 'array',
        ];
    }

    public function textMessages(): HasMany
    {
        return $this->hasMany(TextMessage::class);
    }
}
