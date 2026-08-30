<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeUpgradeTest extends TestCase
{
    use RefreshDatabase;

    protected function enrollStudent(Course $course, array $pivot = []): array
    {
        $student = Student::factory()->create();
        $student->courses()->attach($course->id, array_merge([
            'enrolled_at' => now(),
            'status' => 'active',
            'fee' => $course->fee,
            'original_fee' => $course->fee,
        ], $pivot));

        $enrollment = $student->courses()->where('course_id', $course->id)->first()->pivot;

        return [$student, $enrollment];
    }

    protected function attend(Student $student, Course $course, int $days): void
    {
        for ($i = 0; $i < $days; $i++) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'present',
                'duration' => 1,
                'date' => now()->subDays($days - $i)->toDateString(),
            ]);
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $course = Course::factory()->create(['duration_weeks' => 2]);
        [, $enrollment] = $this->enrollStudent($course);

        $this->get("/enrollments/{$enrollment->id}/upgrade")->assertRedirect('/login');
        $this->post("/enrollments/{$enrollment->id}/upgrade", [])->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_upgrade_a_programme(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['duration_weeks' => 2]);
        [, $enrollment] = $this->enrollStudent($course);

        $this->actingAs($secretary)->get("/enrollments/{$enrollment->id}/upgrade")->assertForbidden();
    }

    public function test_a_director_can_view_the_upgrade_form_within_the_window(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $this->attend($student, $twoWeek, 3);

        $response = $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade");

        $response->assertOk();
        $response->assertSee($fourWeek->name);
    }

    public function test_the_upgrade_form_404s_once_the_window_has_closed(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => $twoWeek->course_type, 'schedule' => $twoWeek->schedule, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $this->attend($student, $twoWeek, 6);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade")->assertNotFound();
    }

    public function test_the_upgrade_form_404s_for_a_completed_enrollment(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => $twoWeek->course_type, 'schedule' => $twoWeek->schedule, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek, ['status' => 'completed']);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade")->assertNotFound();
    }

    public function test_the_upgrade_form_404s_when_no_longer_programme_exists(): void
    {
        $director = User::factory()->director()->create();
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($fourWeek);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade")->assertNotFound();
    }

    public function test_upgrading_charges_only_the_fee_difference_and_preserves_training_progress(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $this->attend($student, $twoWeek, 3);
        Payment::create(['student_id' => $student->id, 'course_id' => $twoWeek->id, 'amount' => 60000, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash', 'status' => 'paid', 'recorded_by' => $director->id]);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 30000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");

        $enrollment->refresh();
        $this->assertSame($fourWeek->id, $enrollment->course_id);
        $this->assertSame(90000.0, $enrollment->fee());
        $this->assertSame(90000.0, $enrollment->amountPaid());
        $this->assertSame(0.0, $enrollment->balance());

        // Training progress must carry over onto the new (longer) course,
        // not reset to zero.
        $this->assertSame(3, $enrollment->attendedDays());
        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'course_id' => $fourWeek->id]);
        $this->assertDatabaseCount('attendances', 3);
        $this->assertDatabaseMissing('attendances', ['course_id' => $twoWeek->id]);

        $this->assertDatabaseHas('programme_upgrade_logs', [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'from_course_id' => $twoWeek->id,
            'to_course_id' => $fourWeek->id,
            'upgraded_by' => $director->id,
            'attended_days_at_upgrade' => 3,
            'previous_fee' => 60000,
            'new_fee' => 90000,
            'amount_charged' => 30000,
        ]);
    }

    public function test_an_existing_discount_carries_over_onto_the_new_fee(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek, ['fee' => 55000, 'original_fee' => 60000, 'discount_amount' => 5000, 'discount_percentage' => 8.33]);

        $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 0,
        ])->assertSessionHasNoErrors();

        $enrollment->refresh();
        // 90,000 new fee minus the same ₦5,000 discount already applied.
        $this->assertSame(85000.0, $enrollment->fee());
    }

    public function test_only_a_longer_course_of_the_same_type_and_schedule_is_a_valid_upgrade_target(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        $wrongType = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'automatic', 'schedule' => 'weekday', 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $wrongType->id,
        ]);

        $response->assertSessionHasErrors('course_id');
        $this->assertSame($twoWeek->id, $enrollment->fresh()->course_id);
    }

    public function test_a_course_the_student_already_holds_a_separate_enrollment_in_is_not_an_eligible_upgrade_target(): void
    {
        // course_student has a unique (course_id, student_id) constraint -
        // upgrading into a course the student is already separately
        // enrolled in would violate it. This otherwise-valid same-type/
        // same-schedule/longer-duration course must not appear as eligible.
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $student->courses()->attach($fourWeek->id, [
            'enrolled_at' => now(), 'status' => 'active', 'fee' => $fourWeek->fee, 'original_fee' => $fourWeek->fee,
        ]);

        $this->assertFalse($enrollment->eligibleUpgradeCourses()->contains('id', $fourWeek->id));

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ]);

        $response->assertSessionHasErrors('course_id');
        $this->assertSame($twoWeek->id, $enrollment->fresh()->course_id);
    }

    public function test_amount_paid_cannot_exceed_the_upgrade_balance(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 40000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('amount_paid');
        $this->assertSame($twoWeek->id, $enrollment->fresh()->course_id);
    }

    public function test_payment_method_is_required_when_an_amount_is_paid(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($twoWeek);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 30000,
        ]);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_a_partial_payment_now_leaves_the_rest_as_a_balance(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        Payment::create(['student_id' => $student->id, 'course_id' => $twoWeek->id, 'amount' => 60000, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash', 'status' => 'paid', 'recorded_by' => $director->id]);

        $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 10000,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $enrollment->refresh();
        $this->assertSame(70000.0, $enrollment->amountPaid());
        $this->assertSame(20000.0, $enrollment->balance());
    }

    public function test_upgrading_past_the_window_is_blocked_even_via_direct_post(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $this->attend($student, $twoWeek, 6);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
        ]);

        $response->assertSessionHasErrors('course_id');
        $this->assertSame($twoWeek->id, $enrollment->fresh()->course_id);
    }

    public function test_registering_into_a_course_with_a_longer_variant_texts_the_student_about_the_upgrade_window(): void
    {
        $user = User::factory()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);

        $this->actingAs($user)->post('/students', [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $twoWeek->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('message_logs', [
            'recipient_name' => 'Jane Doe',
            'purpose' => 'programme_upgrade_window',
        ]);
    }

    public function test_registering_into_the_longest_available_course_sends_no_upgrade_notice(): void
    {
        $user = User::factory()->create();
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);

        $this->actingAs($user)->post('/students', [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $fourWeek->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('message_logs', ['purpose' => 'programme_upgrade_window']);
    }

    public function test_the_student_page_shows_eligible_upgrade_status_and_a_director_only_upgrade_link(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $this->attend($student, $twoWeek, 2);

        $directorResponse = $this->actingAs($director)->get("/students/{$student->id}");
        $directorResponse->assertOk();
        $directorResponse->assertSee('Eligible');
        $directorResponse->assertSee(route('enrollments.upgrade.create', $enrollment->id), false);

        $secretaryResponse = $this->actingAs($secretary)->get("/students/{$student->id}");
        $secretaryResponse->assertOk();
        $secretaryResponse->assertSee('Eligible');
        $secretaryResponse->assertDontSee(route('enrollments.upgrade.create', $enrollment->id), false);
    }

    public function test_the_student_page_shows_closed_status_and_reason_once_the_window_ends(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $this->attend($student, $twoWeek, 6);

        $response = $this->actingAs($director)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee('Programme Upgrade Window Closed');
        $response->assertSee('Five-day upgrade period exceeded');
    }

    public function test_upgrading_reconciles_the_enrollment_status(): void
    {
        $director = User::factory()->director()->create();
        $twoWeek = Course::factory()->create(['duration_weeks' => 2, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 60000, 'status' => 'active']);
        $fourWeek = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 90000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($twoWeek);
        $this->attend($student, $twoWeek, 2);

        $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade", [
            'course_id' => $fourWeek->id,
            'amount_paid' => 30000,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        // 2 attended days can't possibly satisfy a 20-day requirement, so
        // the enrollment must remain active, never flip to completed.
        $this->assertSame('active', $enrollment->fresh()->status);
    }
}
