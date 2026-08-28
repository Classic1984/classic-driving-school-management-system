<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\PushSubscription;
use App\Models\Student;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function subscriptionPayload(string $endpoint = 'https://fcm.googleapis.com/fcm/send/abc123'): array
    {
        return [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'fake-p256dh-key',
                'auth' => 'fake-auth-token',
            ],
        ];
    }

    public function test_an_authenticated_staff_member_can_subscribe(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), $this->subscriptionPayload());

        $response->assertOk();
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'public_key' => 'fake-p256dh-key',
            'auth_token' => 'fake-auth-token',
        ]);
    }

    public function test_an_instructor_can_subscribe(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $instructor = Instructor::factory()->create();
        $instructor->forceFill(['user_id' => $user->id])->save();

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), $this->subscriptionPayload());

        $response->assertOk();
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $user->id]);
    }

    public function test_a_student_can_subscribe(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create();
        $student->forceFill(['user_id' => $user->id])->save();

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), $this->subscriptionPayload());

        $response->assertOk();
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $user->id]);
    }

    public function test_a_guest_cannot_subscribe(): void
    {
        $response = $this->postJson(route('push-subscriptions.store'), $this->subscriptionPayload());

        $response->assertUnauthorized();
        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_re_subscribing_the_same_endpoint_updates_rather_than_duplicates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('push-subscriptions.store'), $this->subscriptionPayload());

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), array_merge(
            $this->subscriptionPayload(),
            ['keys' => ['p256dh' => 'rotated-key', 'auth' => 'rotated-auth']]
        ));

        $response->assertOk();
        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', ['public_key' => 'rotated-key', 'auth_token' => 'rotated-auth']);
    }

    public function test_subscribing_the_same_endpoint_as_a_different_user_reassigns_it(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $this->actingAs($firstUser)->postJson(route('push-subscriptions.store'), $this->subscriptionPayload());

        $this->actingAs($secondUser)->postJson(route('push-subscriptions.store'), $this->subscriptionPayload());

        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $secondUser->id, 'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123']);
    }

    public function test_subscribing_requires_a_valid_payload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), ['endpoint' => 'https://example.test']);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_a_user_can_unsubscribe_their_own_endpoint(): void
    {
        $user = User::factory()->create();
        $subscription = PushSubscription::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $subscription->endpoint]);

        $response->assertOk();
        $this->assertDatabaseMissing('push_subscriptions', ['id' => $subscription->id]);
    }

    public function test_a_user_cannot_unsubscribe_someone_elses_endpoint(): void
    {
        $owner = User::factory()->create();
        $subscription = PushSubscription::factory()->create(['user_id' => $owner->id]);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $subscription->endpoint]);

        $response->assertOk();
        $this->assertDatabaseHas('push_subscriptions', ['id' => $subscription->id]);
    }

    public function test_the_test_notification_endpoint_calls_the_web_push_service_for_the_logged_in_user(): void
    {
        $user = User::factory()->create();

        $this->mock(WebPushService::class, function ($mock) use ($user) {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(fn ($sentUser) => $sentUser->is($user));
        });

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.test'));

        $response->assertOk();
        $response->assertJson(['status' => 'sent']);
    }
}
