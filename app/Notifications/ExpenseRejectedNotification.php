<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseRejectedNotification extends Notification
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
            ->subject("Expense rejected: ₦{$expense->amount}")
            ->greeting('Expense Rejected')
            ->line('Your expense request of ₦'.number_format((float) $expense->amount, 2)." ({$expense->category}) was rejected.")
            ->when($expense->rejection_reason, fn ($mail) => $mail->line("Reason: {$expense->rejection_reason}"))
            ->action('View Expenses', route('expenses.index'));
    }
}
