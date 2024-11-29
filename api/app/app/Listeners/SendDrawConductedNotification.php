<?php

namespace App\Listeners;

use App\Events\DrawConducted;
use App\Mail\DrawConducted as DrawConductedEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDrawConductedNotification implements ShouldQueue
{
    public function __construct() {}

    public function handle(DrawConducted $event): void
    {
        foreach ($event->draw->allocations as $allocation) {
            Mail::to($allocation->from_email)
                ->queue(new DrawConductedEmail($allocation));
        }
    }
}
