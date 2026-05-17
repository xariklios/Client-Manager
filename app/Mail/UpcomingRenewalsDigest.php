<?php

namespace App\Mail;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpcomingRenewalsDigest extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly Collection $projects,
        public readonly int $withinDays,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->projects->count();

        return new Envelope(
            subject: "[Client Manager] {$count} renewal" . ($count === 1 ? '' : 's') . " in the next {$this->withinDays} days",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.upcoming-renewals',
        );
    }
}
