<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingDaysRemainingNotification extends Notification
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
        $remaining = $enrollment->remainingTrainingDays();

        return (new MailMessage)
            ->subject("{$remaining} training day(s) remaining: {$enrollment->student->name}")
            ->greeting('Training Nearing Completion')
            ->line("{$enrollment->student->name} has {$remaining} training day(s) remaining in {$enrollment->course->name}.")
            ->line("Days completed: {$enrollment->attendedDays()} of {$enrollment->course->totalTrainingDays()}.")
            ->action('View Student', route('students.show', $enrollment->student_id));
    }
}
