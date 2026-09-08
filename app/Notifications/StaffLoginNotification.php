<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffLoginNotification extends Notification
{
    use Queueable;

    public function __construct(public User $staff, public ?string $ip) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->staff->name} ({$this->roleLabel()}) just logged in")
            ->greeting('Staff Login')
            ->line("{$this->staff->name}, a {$this->roleLabel()}, just logged in to CDSMS.")
            ->line('Time: '.now()->format('l, M j, Y \a\t g:i A'))
            ->when($this->ip, fn ($mail) => $mail->line("IP address: {$this->ip}"));
    }

    protected function roleLabel(): string
    {
        return ucfirst($this->staff->role);
    }
}
