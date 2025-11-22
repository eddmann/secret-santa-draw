<?php

namespace App\Mail;

use App\Models\Allocation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AllocationGiftIdeasProvided extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Allocation $recipientAllocation
    ) {}

    public function envelope(): Envelope
    {
        $subject = sprintf(
            '%s has provided gift ideas - %s (%s)',
            $this->recipientAllocation->from_name,
            $this->recipientAllocation->draw->group->title,
            $this->recipientAllocation->draw->year
        );

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $id = base64_encode(
            route(
                'draws.show',
                [
                    'group' => $this->recipientAllocation->draw->group->id,
                    'draw' => $this->recipientAllocation->draw->id,
                ]
            )
        );

        $url = sprintf(
            '%s/remote/draws/%s?token=%s',
            env('APP_URL'),
            $id,
            $this->recipientAllocation->secretSanta->from_access_token
        );

        $text = sprintf(
            "Hey %s, %s has updated their gift ideas for the '%s (%s)' Secret Santa draw. View them here: %s",
            $this->recipientAllocation->secretSanta->from_name,
            $this->recipientAllocation->from_name,
            $this->recipientAllocation->draw->group->title,
            $this->recipientAllocation->draw->year,
            $url
        );

        return new Content(htmlString: $text);
    }
}
