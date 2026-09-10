<?php

namespace App\Mail;

use App\Models\CorporateQuotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorporateQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CorporateQuotation $quotation, public string $pdfContent) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quotation {$this->quotation->quotation_number} — Classic Driving School",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.corporate.quotation',
            with: [
                'companyName' => $this->quotation->company->name,
                'total' => number_format($this->quotation->total(), 0),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, "{$this->quotation->quotation_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
