<?php

namespace App\Models;

use App\Enums\TextMessageDirection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextMessage extends Model
{
    use HasFactory;
    use HasUuids;

    public function casts(): array
    {
        return [
            'direction' => TextMessageDirection::class,
        ];
    }

    public function phoneNumber(): BelongsTo
    {
        return $this->belongsTo(PhoneNumber::class);
    }
}
