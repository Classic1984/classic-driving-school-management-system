<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\User;
use App\Services\InstructorOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class InstructorAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function instructorWithAccess(array $overrides = []): Instructor
    {
        $user = User::factory()->create(['role' => 'instructor', 'pin_set_at' => null]);

        $instructor = Instructor::factory()->create(array_merge(['phone' => '08031234567'], $overrides));
        $instructor->forceFill(['user_id' => $user->id])->save();

        return $instructor->fresh();
    }

    public function test_an_unknown_phone_number_is_rejected(): void
    {
        $response = $this->post(route('instructor.login.send-code'), ['phone' => '08000000000']);

        $response->assertSessionHasErrors('phone');
    }

    public function test_a_first_time_login_is_sent_to_otp_verification(): void
    {
        $this->instructorWithAccess();

        $response = $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);

        $response->assertRedirect(route('instructor.verify-otp'));
    }

    public function test_a_returning_instructor_is_sent_straight_to_pin_entry(): void
    {
        $instructor = $this->instructorWithAccess();
        $instructor->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);

        $response->assertRedirect(route('instructor.enter-pin'));
    }

    public function test_completing_otp_verification_sets_the_pin_and_logs_in(): void
    {
        $instructor = $this->instructorWithAccess();
        $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);
        $otp = Cache::get('instructor-otp:2348031234567');

        $response = $this->post(route('instructor.verify-otp.store'), [
            'otp' => $otp,
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);

        $response->assertRedirect(route('instructor.dashboard'));
        $this->assertAuthenticatedAs($instructor->user->fresh());
        $this->assertNotNull($instructor->user->fresh()->pin_set_at);
    }

    public function test_an_incorrect_otp_is_rejected(): void
    {
        $this->instructorWithAccess();
        $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);

        $response = $this->post(route('instructor.verify-otp.store'), [
            'otp' => '000000',
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    public function test_an_otp_cannot_be_reused_after_a_successful_verification(): void
    {
        $this->instructorWithAccess();
        $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);
        $otp = Cache::get('instructor-otp:2348031234567');
        $this->post(route('instructor.verify-otp.store'), ['otp' => $otp, 'pin' => '4321', 'pin_confirmation' => '4321']);
        $this->post(route('instructor.logout'));

        $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);
        $response = $this->post(route('instructor.verify-otp.store'), ['otp' => $otp, 'pin' => '5555', 'pin_confirmation' => '5555']);

        $response->assertSessionHasErrors('otp');
    }

    public function test_a_correct_pin_logs_in_a_returning_instructor(): void
    {
        $instructor = $this->instructorWithAccess();
        $instructor->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();
        $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);

        $response = $this->post(route('instructor.enter-pin.store'), ['pin' => '1234']);

        $response->assertRedirect(route('instructor.dashboard'));
        $this->assertAuthenticatedAs($instructor->user->fresh());
    }

    public function test_an_incorrect_pin_is_rejected(): void
    {
        $instructor = $this->instructorWithAccess();
        $instructor->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();
        $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);

        $response = $this->post(route('instructor.enter-pin.store'), ['pin' => '9999']);

        $response->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_pin_entry_is_rate_limited_after_five_wrong_attempts(): void
    {
        $instructor = $this->instructorWithAccess();
        $instructor->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();
        $this->post(route('instructor.login.send-code'), ['phone' => '08031234567']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('instructor.enter-pin.store'), ['pin' => '0000']);
        }
        $response = $this->post(route('instructor.enter-pin.store'), ['pin' => '1234']);

        $response->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_an_instructor_cannot_reach_the_general_staff_area(): void
    {
        $instructor = $this->instructorWithAccess();
        $instructor->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->actingAs($instructor->user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_regular_staff_cannot_reach_the_instructor_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('instructor.dashboard'));

        $response->assertForbidden();
    }

    public function test_an_instructor_can_reach_their_own_dashboard(): void
    {
        $instructor = $this->instructorWithAccess();
        $instructor->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->actingAs($instructor->user)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertSee($instructor->name);
    }

    public function test_an_instructor_account_cannot_log_in_through_the_normal_staff_login_form(): void
    {
        // Granted through the real flow, not the test helper's shortcut -
        // the guarantee being tested comes from how
        // InstructorAccessController::store() creates the account (an
        // unusable random password), not anything the factory does.
        $courseManager = User::factory()->create();
        $instructor = Instructor::factory()->create(['phone' => '08031234567']);
        $this->actingAs($courseManager)->post(route('instructors.access.store', $instructor));
        $this->post('/logout');

        $response = $this->post('/login', [
            'email' => $instructor->fresh()->user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_the_otp_service_does_not_verify_a_code_it_never_issued(): void
    {
        $service = app(InstructorOtpService::class);

        $this->assertFalse($service->verify('2348031234567', '123456'));
    }
}
