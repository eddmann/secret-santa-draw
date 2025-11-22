<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Allocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllocationGiftIdeasProvided
{
    use Dispatchable, SerializesModels;

    public function __construct(public Allocation $allocation) {}
}
