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
        $secretSantaAllocation = Allocation::where([
            'draw_id' => $event->allocation->draw_id,
            'to_email' => $event->allocation->from_email,
        ])->firstOrFail();

        Mail::to($secretSantaAllocation->from_email)
            ->queue(new AllocationGiftIdeasProvidedMail($event->allocation, $secretSantaAllocation));
    }
}
