<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;

class ReminderController extends Controller
{
    /**
     * The reminder types staff can trigger on demand, mapped to the
     * artisan command that normally only runs on the daily/weekly
     * schedule - and the human label used in the confirmation flash and
     * activity log.
     *
     * @var array<string, array{command: string, label: string}>
     */
    protected const REMINDERS = [
        'balance_reminder' => ['command' => 'app:send-balance-reminder', 'label' => 'Balance Reminder'],
        'theory_class_reminder' => ['command' => 'app:send-theory-class-reminder', 'label' => 'Theory Class Reminder'],
        'lead_follow_up' => ['command' => 'app:send-lead-follow-up-reminder', 'label' => 'Lead Follow-Up'],
        'absence_check_in' => ['command' => 'app:send-absence-check-in-reminder', 'label' => 'Absence Check-In'],
    ];

    /**
     * Manually run one of the scheduled reminder commands right now,
     * instead of waiting for its next scheduled run. Relays the command's
     * own console output (e.g. "Balance reminder sent to 3 of 5
     * student(s).") back as the flash message, so this shares its wording
     * with the scheduled run rather than duplicating the counting logic.
     */
    public function send(string $type): RedirectResponse
    {
        abort_unless(array_key_exists($type, self::REMINDERS), 404);

        $reminder = self::REMINDERS[$type];

        Artisan::call($reminder['command']);

        ActivityLog::record("Manually triggered the {$reminder['label']} reminder");

        return Redirect::route('message-log.index')->with('status', trim(Artisan::output()));
    }
}
