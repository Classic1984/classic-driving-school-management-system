<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * A client can be injected directly (tests do this, to swap in a
     * mock rather than making a real network call) - production code
     * always leaves this null and lets client() build one from config.
     */
    public function __construct(protected ?WebPush $client = null) {}

    /**
     * Whether VAPID keys have been configured for this deployment. Every
     * public method below is a no-op when this is false, the same way
     * TermiiSmsService quietly does nothing without an API key - there's
     * no server to talk to yet, so there's nothing to fail loudly about.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.webpush.vapid_public_key'))
            && filled(config('services.webpush.vapid_private_key'));
    }

    /**
     * Push a notification to every device this user has subscribed from.
     * A subscription the push service reports as gone (the browser
     * unsubscribed, cleared its data, or was uninstalled) is deleted here
     * rather than retried forever.
     */
    public function sendToUser(User $user, string $title, string $body, ?string $url = null): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $subscriptions = $user->pushSubscriptions;

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = $this->client();
        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url]);

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'keys' => [
                        'p256dh' => $subscription->public_key,
                        'auth' => $subscription->auth_token,
                    ],
                ]),
                $payload
            );
        }

        // flush() is a generator - it does its real work (including per-
        // notification payload encryption) lazily as it's iterated, so an
        // encryption or request failure surfaces as an exception from the
        // foreach itself, not as a failed MessageSentReport. One malformed
        // subscription throwing here must never take down a caller that's
        // just trying to notify a student their certificate is ready, so
        // this is caught and logged rather than left to propagate - the
        // cost is that any notifications still queued behind the one that
        // failed are skipped for this call, which is an acceptable
        // degradation for what should be a rare, one-off failure.
        try {
            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    continue;
                }

                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();

                    continue;
                }

                Log::warning('Web push notification failed', [
                    'endpoint' => $report->getEndpoint(),
                    'reason' => $report->getReason(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Web push flush failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Push every Director with app access - the audience for anything
     * landing in the Approval Centre (discount requests, correction
     * requests, assessment recommendations). One sendToUser() call per
     * Director rather than one shared batch, so a bad subscription on one
     * Director's account can't skip the notification for the others.
     */
    public function sendToDirectors(string $title, string $body, ?string $url = null): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        foreach (User::where('role', 'director')->get() as $director) {
            $this->sendToUser($director, $title, $body, $url);
        }
    }

    protected function client(): WebPush
    {
        return $this->client ??= new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.vapid_subject'),
                'publicKey' => config('services.webpush.vapid_public_key'),
                'privateKey' => config('services.webpush.vapid_private_key'),
            ],
        ]);
    }
}
