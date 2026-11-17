<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expense = $this->expense;

        return (new MailMessage)
            ->subject("Expense approval needed: ₦{$expense->amount}")
            ->greeting('Expense Approval Needed')
            ->line("{$expense->requestedBy->name} submitted an expense of ₦".number_format((float) $expense->amount, 2)." ({$expense->category}).")
            ->when($expense->description, fn ($mail) => $mail->line("Description: {$expense->description}"))
            ->line('Nothing is paid out until this is approved or rejected.')
            ->action('Review Request', route('approvals.index'));
    }
}
