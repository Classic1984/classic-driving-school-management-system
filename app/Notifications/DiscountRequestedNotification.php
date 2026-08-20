<?php

namespace App\Notifications;

use App\Models\DiscountRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscountRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public DiscountRequest $discountRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->discountRequest;

        return (new MailMessage)
            ->subject("Discount approval needed: {$request->student->name}")
            ->greeting('Discount Approval Needed')
            ->line("{$request->requestedBy->name} enrolled {$request->student->name} in {$request->course->name} with a requested discount of ₦".number_format((float) $request->discount_amount, 2).'.')
            ->line('Original fee: ₦'.number_format((float) $request->original_fee, 2).' - Requested final fee: ₦'.number_format((float) $request->final_fee, 2))
            ->when($request->reason, fn ($mail) => $mail->line("Reason: {$request->reason}"))
            ->line('The enrollment is active at the full fee until this discount is approved or rejected.')
            ->action('Review Request', route('approvals.index'));
    }
}
