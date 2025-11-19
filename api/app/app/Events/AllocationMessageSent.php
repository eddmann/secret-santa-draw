<?php

namespace App\Events;

use App\Models\AllocationMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllocationMessageSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AllocationMessage $message,
    ) {}
}
