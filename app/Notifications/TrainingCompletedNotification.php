<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent the moment an enrollment completes. Certificate issuance happens in
 * the very same step as completion (Enrollment::markCompleted()), so there
 * is no separate "certificate ready" moment to notify about later - this
 * single message covers both.
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

        return (new MailMessage)
            ->subject("Training completed: {$enrollment->student->name}")
            ->greeting('Training Completed')
            ->line("{$enrollment->student->name} has completed the required {$enrollment->course->totalTrainingDays()} training day(s) for {$enrollment->course->name}.")
            ->line('A certificate has been issued and is ready for collection at the school office.')
            ->action('View Student', route('students.show', $enrollment->student_id));
    }
}
