<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationMessage extends Model
{
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'allocation_id',
        'is_from_secret_santa',
        'message',
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }
}
