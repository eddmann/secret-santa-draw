<?php

namespace App\Events;

use App\Models\Draw;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DrawConducted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Draw $draw,
    ) {}
}
