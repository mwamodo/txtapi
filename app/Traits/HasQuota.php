<?php

namespace App\Traits;

trait HasQuota
{
    public function hasQuotaAvailable(): bool
    {
        return $this->quota_remaining > 0;
    }

    public function decrementQuota(int $amount = 1): void
    {
        $this->quota_remaining = max(0, $this->quota_remaining - $amount);
        $this->save();
    }
}
