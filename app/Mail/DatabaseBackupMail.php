<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $path)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CDSMS Database Backup - '.now()->format('Y-m-d'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.database-backup',
            with: ['date' => now()->format('Y-m-d H:i')],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->path)->as(basename($this->path)),
        ];
    }
}
