<?php

namespace App\Mail;

use App\Models\CorporateInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorporateInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CorporateInvoice $invoice, public string $pdfContent) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} — Classic Driving School",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.corporate.invoice',
            with: [
                'companyName' => $this->invoice->company->name,
                'amountDue' => number_format($this->invoice->total(), 0),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, "{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
