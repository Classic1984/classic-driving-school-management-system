<?php

namespace App\Notifications;

use App\Models\StudentCorrectionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentCorrectionRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public StudentCorrectionRequest $correctionRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->correctionRequest;
        $student = $request->student;

        return (new MailMessage)
            ->subject("Correction requested: {$student->name}")
            ->greeting('Correction Requested')
            ->line("{$request->requestedBy->name} has requested a change to {$student->name}'s {$request->fieldLabel()}.")
            ->line("Current value: {$request->current_value}")
            ->line("Requested value: {$request->requested_value}")
            ->when($request->reason, fn ($mail) => $mail->line("Reason: {$request->reason}"))
            ->action('Review Request', route('student-correction-requests.index'));
    }
}
