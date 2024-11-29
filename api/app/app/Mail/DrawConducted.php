<?php

namespace App\Mail;

use App\Models\Allocation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DrawConducted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Allocation $allocation) {}

    public function envelope(): Envelope
    {
        $subject = sprintf(
            "The '%s (%s)' draw has been done!",
            $this->allocation->draw->group->title,
            $this->allocation->draw->year
        );

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $id = base64_encode(
            route(
                'draws.show',
                [
                    'group' => $this->allocation->draw->group->id,
                    'draw' => $this->allocation->draw->id,
                ]
            )
        );

        $url = sprintf(
            '%s/remote/draws/%s?token=%s',
            env('APP_URL'),
            $id,
            $this->allocation->from_access_token
        );

        $text = sprintf(
            "Hey %s, the '%s (%s)' Secret Santa draw has been done, see who you're Secret Santa for: %s",
            $this->allocation->from_name,
            $this->allocation->draw->group->title,
            $this->allocation->draw->year,
            $url
        );

        return new Content(htmlString: $text);
    }
}
