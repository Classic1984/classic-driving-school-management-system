<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseApprovedNotification extends Notification
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
            ->subject("Expense approved: ₦{$expense->amount}")
            ->greeting('Expense Approved')
            ->line('Your expense request of ₦'.number_format((float) $expense->amount, 2)." ({$expense->category}) has been approved.")
            ->line("It's now cleared to be paid - it won't enter the expenditure totals until it's actually marked as paid.")
            ->action('View Expenses', route('expenses.index'));
    }
}
