<?php

namespace App\Mail;

use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpcomingChargeReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Charge $charge,
        public readonly int $daysUntilDue,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Upcoming Payment Reminder: ' . $this->charge->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.upcoming-charge-reminder',
        );
    }
}
