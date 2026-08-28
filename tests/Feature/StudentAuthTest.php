<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Services\StudentOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StudentAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function studentWithAccess(array $overrides = []): Student
    {
        $user = User::factory()->create(['role' => 'student', 'pin_set_at' => null]);

        $student = Student::factory()->create(array_merge(['phone' => '08031234567'], $overrides));
        $student->forceFill(['user_id' => $user->id])->save();

        return $student->fresh();
    }

    public function test_an_unknown_phone_number_is_rejected(): void
    {
        $response = $this->post(route('student.login.send-code'), ['phone' => '08000000000']);

        $response->assertSessionHasErrors('phone');
    }

    public function test_a_first_time_login_is_sent_to_otp_verification(): void
    {
        $this->studentWithAccess();

        $response = $this->post(route('student.login.send-code'), ['phone' => '08031234567']);

        $response->assertRedirect(route('student.verify-otp'));
    }

    public function test_a_returning_student_is_sent_straight_to_pin_entry(): void
    {
        $student = $this->studentWithAccess();
        $student->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->post(route('student.login.send-code'), ['phone' => '08031234567']);

        $response->assertRedirect(route('student.enter-pin'));
    }

    public function test_completing_otp_verification_sets_the_pin_and_logs_in(): void
    {
        $student = $this->studentWithAccess();
        $this->post(route('student.login.send-code'), ['phone' => '08031234567']);
        $otp = Cache::get('student-otp:2348031234567');

        $response = $this->post(route('student.verify-otp.store'), [
            'otp' => $otp,
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($student->user->fresh());
        $this->assertNotNull($student->user->fresh()->pin_set_at);
    }

    public function test_an_incorrect_otp_is_rejected(): void
    {
        $this->studentWithAccess();
        $this->post(route('student.login.send-code'), ['phone' => '08031234567']);

        $response = $this->post(route('student.verify-otp.store'), [
            'otp' => '000000',
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    public function test_an_otp_cannot_be_reused_after_a_successful_verification(): void
    {
        $this->studentWithAccess();
        $this->post(route('student.login.send-code'), ['phone' => '08031234567']);
        $otp = Cache::get('student-otp:2348031234567');
        $this->post(route('student.verify-otp.store'), ['otp' => $otp, 'pin' => '4321', 'pin_confirmation' => '4321']);
        $this->post(route('student.logout'));

        $this->post(route('student.login.send-code'), ['phone' => '08031234567']);
        $response = $this->post(route('student.verify-otp.store'), ['otp' => $otp, 'pin' => '5555', 'pin_confirmation' => '5555']);

        $response->assertSessionHasErrors('otp');
    }

    public function test_a_correct_pin_logs_in_a_returning_student(): void
    {
        $student = $this->studentWithAccess();
        $student->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();
        $this->post(route('student.login.send-code'), ['phone' => '08031234567']);

        $response = $this->post(route('student.enter-pin.store'), ['pin' => '1234']);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($student->user->fresh());
    }

    public function test_an_incorrect_pin_is_rejected(): void
    {
        $student = $this->studentWithAccess();
        $student->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();
        $this->post(route('student.login.send-code'), ['phone' => '08031234567']);

        $response = $this->post(route('student.enter-pin.store'), ['pin' => '9999']);

        $response->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_pin_entry_is_rate_limited_after_five_wrong_attempts(): void
    {
        $student = $this->studentWithAccess();
        $student->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();
        $this->post(route('student.login.send-code'), ['phone' => '08031234567']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('student.enter-pin.store'), ['pin' => '0000']);
        }
        $response = $this->post(route('student.enter-pin.store'), ['pin' => '1234']);

        $response->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_a_student_cannot_reach_the_general_staff_area(): void
    {
        $student = $this->studentWithAccess();
        $student->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->actingAs($student->user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_regular_staff_cannot_reach_the_student_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('student.dashboard'));

        $response->assertForbidden();
    }

    public function test_an_instructor_cannot_reach_the_student_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);

        $response = $this->actingAs($user)->get(route('student.dashboard'));

        $response->assertForbidden();
    }

    public function test_a_student_cannot_reach_the_instructor_dashboard(): void
    {
        $student = $this->studentWithAccess();
        $student->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->actingAs($student->user)->get(route('instructor.dashboard'));

        $response->assertForbidden();
    }

    public function test_a_student_can_reach_their_own_dashboard(): void
    {
        $student = $this->studentWithAccess();
        $student->user->forceFill(['pin' => '1234', 'pin_set_at' => now()])->save();

        $response = $this->actingAs($student->user)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee($student->name);
    }

    public function test_a_student_account_cannot_log_in_through_the_normal_staff_login_form(): void
    {
        // Granted through the real flow, not the test helper's shortcut -
        // the guarantee being tested comes from how
        // StudentAccessController::store() creates the account (an
        // unusable random password), not anything the factory does.
        $courseManager = User::factory()->create();
        $student = Student::factory()->create(['phone' => '08031234567']);
        $this->actingAs($courseManager)->post(route('students.access.store', $student));
        $this->post('/logout');

        $response = $this->post('/login', [
            'email' => $student->fresh()->user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_the_otp_service_does_not_verify_a_code_it_never_issued(): void
    {
        $service = app(StudentOtpService::class);

        $this->assertFalse($service->verify('2348031234567', '123456'));
    }
}
