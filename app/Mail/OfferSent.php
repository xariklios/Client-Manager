<?php

namespace App\Mail;

use App\Models\Offer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferSent extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Offer $offer) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Προσφορά: ' . $this->offer->title);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.offer-sent');
    }

    public function attachments(): array
    {
        $offer      = $this->offer->load(['client', 'project', 'items']);
        $pdf        = Pdf::loadView('offers.pdf', compact('offer'))->output();
        $filename   = 'offer-' . str_pad($offer->id, 4, '0', STR_PAD_LEFT) . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf, $filename)->withMime('application/pdf'),
        ];
    }
}
