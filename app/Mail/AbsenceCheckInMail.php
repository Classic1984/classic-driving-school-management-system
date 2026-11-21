<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The email twin of the SMS/WhatsApp absence check-in reminder - sent to
 * every student who has gone 4+ days without a training login, in
 * addition to whichever phone channel reaches them.
 */
class AbsenceCheckInMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Student $student) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We miss you at Classic Driving School',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.students.absence-check-in',
            with: [
                'studentName' => $this->student->name,
            ],
        );
    }
}
