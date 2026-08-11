<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GracePeriodEndingSoonNotification extends Notification
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
            ->subject("Payment due tomorrow: {$enrollment->student->name}")
            ->greeting('Grace Period Ending Soon')
            ->line("{$enrollment->student->name}'s payment grace period for {$enrollment->course->name} ends tomorrow ({$enrollment->due_date->format('Y-m-d')}).")
            ->line('Outstanding balance: ₦'.number_format($enrollment->balance(), 2))
            ->line('If the balance is not cleared by then, training will be automatically locked.')
            ->action('View Student', route('students.show', $enrollment->student_id));
    }
}
