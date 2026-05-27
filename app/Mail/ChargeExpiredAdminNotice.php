<?php

namespace App\Mail;

use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChargeExpiredAdminNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Charge $charge) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Λήξη υπηρεσίας: ' . $this->charge->title . ' — ' . $this->charge->client->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.charge-expired-admin',
        );
    }
}
