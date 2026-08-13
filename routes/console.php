<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:refresh-enrollment-locks')->daily();
Schedule::command('backup:database')->twiceDaily(2, 14);
Schedule::command('app:send-theory-class-reminder')->weeklyOn(4, '08:00');
Schedule::command('app:send-balance-reminder')->weeklyOn(1, '09:00');
// Checked daily; SendLeadFollowUpReminder itself only texts a lead once at
// least 4 days have passed since they were logged (or last reminded).
Schedule::command('app:send-lead-follow-up-reminder')->dailyAt('10:00');
