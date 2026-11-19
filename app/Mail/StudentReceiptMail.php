<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The welcome email sent when a student's registration includes an
 * initial payment - a thank-you note with their official receipt
 * attached as a PDF, the same document they'd get by printing or
 * downloading it from their payment page.
 */
class StudentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Student $student, public Payment $payment, public string $pdfContent) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to Classic Driving School — Your Receipt ({$this->payment->receipt_number})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.students.welcome-receipt',
            with: [
                'studentName' => $this->student->name,
                'amountPaid' => number_format((float) $this->payment->amount, 2),
                'receiptNumber' => $this->payment->receipt_number,
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
