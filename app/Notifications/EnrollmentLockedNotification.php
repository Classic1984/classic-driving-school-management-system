<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentLockedNotification extends Notification
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
            ->subject("Training locked: {$enrollment->student->name}")
            ->greeting('Enrollment Locked')
            ->line("{$enrollment->student->name}'s enrollment in {$enrollment->course->name} has been locked due to an overdue balance.")
            ->line('Outstanding balance: ₦'.number_format($enrollment->balance(), 2))
            ->line('Payment was due on: '.$enrollment->due_date->format('Y-m-d'))
            ->action('View Student', route('students.show', $enrollment->student_id));
    }
}
