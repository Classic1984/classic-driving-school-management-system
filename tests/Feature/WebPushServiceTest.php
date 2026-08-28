<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;
use Tests\TestCase;

class WebPushServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function configureFakeVapidKeys(): void
    {
        config([
            'services.webpush.vapid_public_key' => 'fake-public-key',
            'services.webpush.vapid_private_key' => 'fake-private-key',
        ]);
    }

    public function test_it_is_not_configured_without_vapid_keys(): void
    {
        config(['services.webpush.vapid_public_key' => null, 'services.webpush.vapid_private_key' => null]);

        $this->assertFalse((new WebPushService)->isConfigured());
    }

    public function test_it_is_configured_with_both_vapid_keys(): void
    {
        $this->configureFakeVapidKeys();

        $this->assertTrue((new WebPushService)->isConfigured());
    }

    public function test_sending_does_nothing_when_not_configured(): void
    {
        config(['services.webpush.vapid_public_key' => null, 'services.webpush.vapid_private_key' => null]);

        $client = \Mockery::mock(WebPush::class);
        $client->shouldNotReceive('queueNotification');
        $client->shouldNotReceive('flush');

        $user = User::factory()->create();
        PushSubscription::factory()->create(['user_id' => $user->id]);

        (new WebPushService($client))->sendToUser($user, 'Title', 'Body');
    }

    public function test_sending_does_nothing_when_the_user_has_no_subscriptions(): void
    {
        $this->configureFakeVapidKeys();

        $client = \Mockery::mock(WebPush::class);
        $client->shouldNotReceive('queueNotification');
        $client->shouldNotReceive('flush');

        $user = User::factory()->create();

        (new WebPushService($client))->sendToUser($user, 'Title', 'Body');
    }

    public function test_sending_queues_and_flushes_a_notification_for_each_subscription(): void
    {
        $this->configureFakeVapidKeys();

        $user = User::factory()->create();
        PushSubscription::factory()->count(2)->create(['user_id' => $user->id]);

        $client = \Mockery::mock(WebPush::class);
        $client->shouldReceive('queueNotification')->twice();
        $client->shouldReceive('flush')->once()->andReturn((function () {
            yield from [];
        })());

        (new WebPushService($client))->sendToUser($user, 'Title', 'Body', 'https://example.test');
    }

    public function test_an_expired_subscription_is_deleted_when_the_push_service_reports_it_gone(): void
    {
        $this->configureFakeVapidKeys();

        $user = User::factory()->create();
        $subscription = PushSubscription::factory()->create(['user_id' => $user->id]);

        $report = \Mockery::mock(MessageSentReport::class);
        $report->shouldReceive('isSuccess')->andReturn(false);
        $report->shouldReceive('isSubscriptionExpired')->andReturn(true);
        $report->shouldReceive('getEndpoint')->andReturn($subscription->endpoint);

        $client = \Mockery::mock(WebPush::class);
        $client->shouldReceive('queueNotification')->once();
        $client->shouldReceive('flush')->once()->andReturn((function () use ($report) {
            yield $report;
        })());

        (new WebPushService($client))->sendToUser($user, 'Title', 'Body');

        $this->assertDatabaseMissing('push_subscriptions', ['id' => $subscription->id]);
    }

    public function test_an_exception_while_flushing_does_not_propagate_out_of_sendtouser(): void
    {
        // flush() is a generator - it does its real work (payload
        // encryption included) lazily as it's iterated, so a malformed
        // subscription's bad key material surfaces as an exception thrown
        // from the foreach itself, not as a failed MessageSentReport. This
        // is a regression test for exactly that: sendToUser() must never
        // let a single bad subscription crash the caller (e.g. the code
        // sending a "certificate ready" notification).
        $this->configureFakeVapidKeys();

        $user = User::factory()->create();
        PushSubscription::factory()->create(['user_id' => $user->id]);

        $client = \Mockery::mock(WebPush::class);
        $client->shouldReceive('queueNotification')->once();
        $client->shouldReceive('flush')->once()->andReturn((function () {
            throw new \RuntimeException('Unable to compute the agreement key.');
            yield; // @phpstan-ignore-line - unreachable, satisfies the Generator return type.
        })());

        (new WebPushService($client))->sendToUser($user, 'Title', 'Body');

        $this->assertTrue(true);
    }

    public function test_sending_to_directors_pushes_every_director_with_app_access(): void
    {
        $this->configureFakeVapidKeys();

        $director1 = User::factory()->director()->create();
        $director2 = User::factory()->director()->create();
        $nonDirector = User::factory()->secretary()->create();
        PushSubscription::factory()->create(['user_id' => $director1->id]);
        PushSubscription::factory()->create(['user_id' => $director2->id]);
        PushSubscription::factory()->create(['user_id' => $nonDirector->id]);

        $client = \Mockery::mock(WebPush::class);
        $client->shouldReceive('queueNotification')->twice();
        $client->shouldReceive('flush')->twice()->andReturn((function () {
            yield from [];
        })());

        (new WebPushService($client))->sendToDirectors('Title', 'Body', 'https://example.test');
    }

    public function test_sending_to_directors_does_nothing_when_not_configured(): void
    {
        config(['services.webpush.vapid_public_key' => null, 'services.webpush.vapid_private_key' => null]);

        $director = User::factory()->director()->create();
        PushSubscription::factory()->create(['user_id' => $director->id]);

        $client = \Mockery::mock(WebPush::class);
        $client->shouldNotReceive('queueNotification');
        $client->shouldNotReceive('flush');

        (new WebPushService($client))->sendToDirectors('Title', 'Body');
    }

    public function test_a_non_expired_failure_does_not_delete_the_subscription(): void
    {
        $this->configureFakeVapidKeys();

        $user = User::factory()->create();
        $subscription = PushSubscription::factory()->create(['user_id' => $user->id]);

        $report = \Mockery::mock(MessageSentReport::class);
        $report->shouldReceive('isSuccess')->andReturn(false);
        $report->shouldReceive('isSubscriptionExpired')->andReturn(false);
        $report->shouldReceive('getEndpoint')->andReturn($subscription->endpoint);
        $report->shouldReceive('getReason')->andReturn('Temporary failure');

        $client = \Mockery::mock(WebPush::class);
        $client->shouldReceive('queueNotification')->once();
        $client->shouldReceive('flush')->once()->andReturn((function () use ($report) {
            yield $report;
        })());

        (new WebPushService($client))->sendToUser($user, 'Title', 'Body');

        $this->assertDatabaseHas('push_subscriptions', ['id' => $subscription->id]);
    }
}
