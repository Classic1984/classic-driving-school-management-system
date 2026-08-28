<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Register (or re-register) this browser's push subscription against
     * the logged-in user - staff, instructor, or student alike, whoever
     * is authenticated when the "Enable Notifications" prompt is
     * accepted. Upserted on endpoint: re-subscribing the same browser
     * (after a token refresh, or after logging in as someone else on a
     * shared device) reassigns the existing row instead of colliding with
     * its unique constraint.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $request->user()->id,
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
            ]
        );

        return response()->json(['status' => 'subscribed']);
    }

    /**
     * Drop this browser's subscription. Scoped to the logged-in user's own
     * subscriptions, so submitting someone else's endpoint (which would
     * have to be guessed - it's an opaque, unpredictable push-service URL)
     * can't delete a subscription that isn't yours.
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => ['required', 'string']]);

        $request->user()->pushSubscriptions()->where('endpoint', $data['endpoint'])->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    /**
     * Send the logged-in user a test notification on every device they've
     * subscribed from, so they can confirm push actually reaches them
     * before relying on it.
     */
    public function test(Request $request, WebPushService $webPush): JsonResponse
    {
        $webPush->sendToUser($request->user(), 'Test Notification', 'Push notifications are working on this device.', url('/'));

        return response()->json(['status' => 'sent']);
    }
}
