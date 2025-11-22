<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AllocationGiftIdeasProvided;
use App\Mail\AllocationGiftIdeasProvided as AllocationGiftIdeasProvidedMail;
use App\Models\Allocation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAllocationGiftIdeasProvidedNotification implements ShouldQueue
{
    public function handle(AllocationGiftIdeasProvided $event): void
    {
        Mail::to($event->allocation->secretSanta->from_email)
            ->queue(new AllocationGiftIdeasProvidedMail($event->allocation));
    }
}
