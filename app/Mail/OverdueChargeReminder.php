<?php

namespace App\Mail;

use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueChargeReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Charge $charge,
        public readonly int $daysOverdue,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overdue Payment: ' . $this->charge->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.overdue-charge-reminder',
        );
    }
}
