<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification
{
    use Queueable;

    /**
     * @param  'upcoming'|'due_today'  $stage
     */
    public function __construct(public Enrollment $enrollment, public string $stage) {}

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
        $dueDate = $enrollment->due_date->format('Y-m-d');

        $message = (new MailMessage)
            ->subject($this->stage === 'due_today'
                ? "Payment due today: {$enrollment->course->name}"
                : "Payment reminder: {$enrollment->course->name}")
            ->greeting("Hello {$enrollment->student->name},");

        if ($this->stage === 'due_today') {
            $message->line("Your payment for {$enrollment->course->name} is due today ({$dueDate}).");
        } else {
            $message->line("This is a reminder that your payment for {$enrollment->course->name} is due on {$dueDate}.");
        }

        return $message
            ->line('Outstanding balance: ₦'.number_format($enrollment->balance(), 2))
            ->line('Please make your payment at the school office to avoid your training being locked.');
    }
}
