<?php

namespace App\Notifications;

use App\Models\AssessmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public AssessmentRequest $assessmentRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->assessmentRequest;

        return (new MailMessage)
            ->subject("Assessment approval needed: {$request->student->name}")
            ->greeting('Assessment Approval Needed')
            ->line("{$request->requestedBy->name} recorded a {$request->result} final practical assessment for {$request->student->name} ({$request->course->name}).")
            ->when($request->score !== null, fn ($mail) => $mail->line("Score: {$request->score}"))
            ->when($request->remarks, fn ($mail) => $mail->line("Remarks: {$request->remarks}"))
            ->line('No certificate will be issued until this result is confirmed.')
            ->action('Review Request', route('approvals.index'));
    }
}
