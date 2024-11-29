<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Draw extends Model
{
    protected $fillable = [
        'group_id',
        'year',
        'description',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    public function allocationFor(?int $userId, ?string $accessToken): ?Allocation
    {
        $found = null;

        foreach ($this->allocations as $allocation) {
            if ($allocation->from_user_id !== null && $allocation->from_user_id === $userId) {
                $found = $allocation;
            }
            if ($allocation->from_access_token === $accessToken) {
                return $allocation;
            }
        }

        return $found;
    }
}
