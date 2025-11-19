<?php

namespace App\Listeners;

use App\Events\AllocationMessageSent;
use App\Mail\AllocationMessageReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAllocationMessageNotification implements ShouldQueue
{
    public function __construct() {}

    public function handle(AllocationMessageSent $event): void
    {
        $message = $event->message;

        Mail::to($message->is_from_secret_santa ? $message->allocation->to_email : $message->allocation->from_email)
            ->queue(new AllocationMessageReceived($message));
    }
}
