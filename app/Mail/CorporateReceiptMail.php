<?php

namespace App\Mail;

use App\Models\CorporatePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorporateReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CorporatePayment $payment, public string $pdfContent) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Receipt {$this->payment->receipt_number} — Classic Driving School",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.corporate.receipt',
            with: [
                'companyName' => $this->payment->invoice->company->name,
                'amountPaid' => number_format((float) $this->payment->amount, 0),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, "{$this->payment->receipt_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
