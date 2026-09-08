<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\StaffLoginNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class StaffLoginNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_directors_are_notified_when_a_secretary_logs_in(): void
    {
        Notification::fake();

        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();

        $this->post('/login', ['email' => $secretary->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertSentTo($director, StaffLoginNotification::class);
    }

    public function test_other_directors_are_notified_when_a_director_logs_in(): void
    {
        Notification::fake();

        $loggingIn = User::factory()->director()->create();
        $otherDirector = User::factory()->director()->create();

        $this->post('/login', ['email' => $loggingIn->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertSentTo($otherDirector, StaffLoginNotification::class);
    }

    public function test_a_director_is_never_notified_about_their_own_login(): void
    {
        Notification::fake();

        $director = User::factory()->director()->create();

        $this->post('/login', ['email' => $director->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertNotSentTo($director, StaffLoginNotification::class);
    }

    public function test_no_notification_is_sent_for_an_instructor_or_student_login(): void
    {
        Notification::fake();

        $director = User::factory()->director()->create();
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->post('/login', ['email' => $instructor->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertNothingSentTo($director);
    }

    public function test_no_notification_is_sent_when_no_other_director_exists(): void
    {
        Notification::fake();

        $secretary = User::factory()->secretary()->create();

        $this->post('/login', ['email' => $secretary->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertNothingSent();
    }

    public function test_a_two_factor_login_is_not_notified_until_the_challenge_is_completed(): void
    {
        Notification::fake();

        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        $secretary = User::factory()->secretary()->create([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode([]),
            'two_factor_confirmed_at' => now(),
        ]);
        $director = User::factory()->director()->create();

        // Password verified, but the challenge hasn't been completed yet -
        // must not notify on this half-finished login.
        $this->post('/login', ['email' => $secretary->email, 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'));

        Notification::assertNothingSentTo($director);

        // Completing the challenge is the real, final login.
        $this->post('/two-factor-challenge', [
            'code' => $google2fa->getCurrentOtp($secretary->fresh()->two_factor_secret),
        ])->assertRedirect(route('dashboard', absolute: false));

        Notification::assertSentTo($director, StaffLoginNotification::class);
    }
}
