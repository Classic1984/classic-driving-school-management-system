<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent the moment an enrollment completes. Enrollment::markCompleted() calls
 * maybeIssueCertificate() before sending this, so hasCertificate() below
 * reflects the real outcome of that same call - but a certificate is only
 * actually issued at this point if a passing final assessment was already
 * on file (see maybeIssueCertificate()'s docblock); the common case is that
 * it isn't yet, and the certificate follows once the assessment is
 * recorded.
 */
class TrainingCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public Enrollment $enrollment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $enrollment = $this->enrollment;

        $message = (new MailMessage)
            ->subject("Training completed: {$enrollment->student->name}")
            ->greeting('Training Completed')
            ->line("{$enrollment->student->name} has completed the required {$enrollment->course->totalTrainingDays()} training day(s) for {$enrollment->course->name}.");

        $message = $enrollment->hasCertificate()
            ? $message->line('A certificate has been issued and is ready for collection at the school office.')
            : $message->line('Their certificate will be issued once their final practical assessment is confirmed.');

        return $message->action('View Student', route('students.show', $enrollment->student_id));
    }
}
