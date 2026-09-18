<?php

namespace App\Notifications;

use App\Models\EnrollmentUpgradeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentUpgradeRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public EnrollmentUpgradeRequest $upgradeRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->upgradeRequest;
        $typeLabel = $request->upgrade_type === 'tier' ? 'tier upgrade' : 'programme upgrade';

        return (new MailMessage)
            ->subject("Upgrade approval needed: {$request->student->name}")
            ->greeting('Upgrade Approval Needed')
            ->line("{$request->requestedBy->name} requested a {$typeLabel} for {$request->student->name}, from {$request->fromCourse->name} to {$request->toCourse->name}.")
            ->line('Current fee: ₦'.number_format((float) $request->previous_fee, 2).' - New fee: ₦'.number_format((float) $request->new_fee, 2).' - Balance to collect: ₦'.number_format((float) $request->upgrade_cost, 2))
            ->line('The enrollment stays on its current programme until this request is approved or rejected.')
            ->action('Review Request', route('approvals.index'));
    }
}
