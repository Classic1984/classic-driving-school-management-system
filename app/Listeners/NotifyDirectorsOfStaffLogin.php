<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\StaffLoginNotification;
use App\Services\WebPushService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Notification;

class NotifyDirectorsOfStaffLogin
{
    public function __construct(protected WebPushService $webPush) {}

    /**
     * Let every other Director know whenever a Secretary or Director logs
     * in - a Director is never notified about their own login.
     *
     * Two-factor-enabled accounts fire this event twice: once the moment
     * the password is verified (Auth::attempt(), inside
     * AuthenticatedSessionController::store()), immediately followed by a
     * forced logout back to the two-factor challenge, and again once that
     * challenge is actually completed (TwoFactorChallengeController). Only
     * the session in the challenge-completion request still carries
     * "login.id" at the moment this listener runs (it's forgotten right
     * after), so that's how the premature, not-yet-verified login is told
     * apart from the real one.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User || ! in_array($user->role, ['director', 'secretary'], true)) {
            return;
        }

        if ($user->hasEnabledTwoFactorAuthentication() && ! request()?->session()->has('login.id')) {
            return;
        }

        $recipients = User::where('role', 'director')->where('id', '!=', $user->id)->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new StaffLoginNotification($user, request()?->ip()));

        // sendToUser() per recipient rather than sendToDirectors(), so the
        // director who just logged in doesn't push-notify themselves
        // either - same excluded-self recipient list as the mail above.
        foreach ($recipients as $recipient) {
            $this->webPush->sendToUser(
                $recipient,
                'Staff Login',
                "{$user->name} (".ucfirst($user->role).') just logged in.',
            );
        }
    }
}
