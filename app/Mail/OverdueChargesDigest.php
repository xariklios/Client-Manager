<?php

namespace App\Mail;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueChargesDigest extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly Collection $charges,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->charges->count();

        return new Envelope(
            subject: "[Client Manager] {$count} overdue charge" . ($count === 1 ? '' : 's'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.overdue-charges',
        );
    }
}
