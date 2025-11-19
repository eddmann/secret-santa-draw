<?php

namespace App\Mail;

use App\Models\AllocationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AllocationMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AllocationMessage $message
    ) {}

    public function envelope(): Envelope
    {
        $sender = $this->getSenderLabel();

        $subject = sprintf(
            'New message from %s - %s (%s)',
            $sender,
            $this->message->allocation->draw->group->title,
            $this->message->allocation->draw->year
        );

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $sender = $this->getSenderLabel();
        $recipientName = $this->getRecipientName();
        $recipientToken = $this->getRecipientToken();

        $id = base64_encode(
            route(
                'draws.show',
                [
                    'group' => $this->message->allocation->draw->group->id,
                    'draw' => $this->message->allocation->draw->id,
                ]
            )
        );

        $url = sprintf(
            '%s/remote/draws/%s?token=%s',
            env('APP_URL'),
            $id,
            $recipientToken
        );

        $text = sprintf(
            "Hey %s, you have a new message from %s in the '%s (%s)' Secret Santa draw. View it here: %s",
            $recipientName,
            $sender,
            $this->message->allocation->draw->group->title,
            $this->message->allocation->draw->year,
            $url
        );

        return new Content(htmlString: $text);
    }

    private function getSenderLabel(): string
    {
        return $this->message->is_from_secret_santa ? 'Your Secret Santa' : $this->message->allocation->to_name;
    }

    private function getRecipientName(): string
    {
        return $this->message->is_from_secret_santa ? $this->message->allocation->to_name : $this->message->allocation->from_name;
    }

    private function getRecipientToken(): string
    {
        if ($this->message->is_from_secret_santa) {
            // Message is from Secret Santa to recipient, need recipient's access token
            $recipientAllocation = \App\Models\Allocation::where([
                'draw_id' => $this->message->allocation->draw_id,
                'from_email' => $this->message->allocation->to_email,
            ])->firstOrFail();

            return $recipientAllocation->from_access_token;
        }

        // Message is from recipient to Secret Santa, use Secret Santa's token
        return $this->message->allocation->from_access_token;
    }
}
