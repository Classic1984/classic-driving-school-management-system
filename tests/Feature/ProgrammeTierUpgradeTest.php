<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeTierUpgradeTest extends TestCase
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
        $course = Course::factory()->create(['duration_weeks' => 4]);
        [, $enrollment] = $this->enrollStudent($course);

        $this->get("/enrollments/{$enrollment->id}/upgrade-tier")->assertRedirect('/login');
        $this->post("/enrollments/{$enrollment->id}/upgrade-tier", [])->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_upgrade_a_programme_tier(): void
    {
        $secretary = User::factory()->secretary()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        Course::factory()->create(['tier' => 'vip', 'fee' => 125000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($standard);

        $this->actingAs($secretary)->get("/enrollments/{$enrollment->id}/upgrade-tier")->assertForbidden();
    }

    public function test_a_director_can_view_the_tier_upgrade_form_regardless_of_schedule_or_type(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 95000, 'status' => 'active']);
        $weekend = Course::factory()->create(['tier' => 'weekend', 'course_type' => 'both', 'schedule' => 'weekend', 'fee' => 125000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($standard);

        $response = $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade-tier");

        $response->assertOk();
        $response->assertSee($weekend->name);
    }

    public function test_the_tier_upgrade_form_stays_open_well_past_the_five_day_ordinary_upgrade_window(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        Course::factory()->create(['tier' => 'executive', 'fee' => 155000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($standard);
        $this->attend($student, $standard, 15);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade-tier")->assertOk();
    }

    public function test_the_tier_upgrade_form_404s_for_a_completed_enrollment(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        Course::factory()->create(['tier' => 'vip', 'fee' => 125000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($standard, ['status' => 'completed']);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade-tier")->assertNotFound();
    }

    public function test_the_tier_upgrade_form_404s_for_a_locked_enrollment(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        Course::factory()->create(['tier' => 'vip', 'fee' => 125000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($standard, ['status' => 'locked', 'locked_reason' => 'overdue_balance']);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade-tier")->assertNotFound();
    }

    public function test_the_tier_upgrade_form_404s_when_no_pricier_tier_exists(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        // Cheaper than the current course, so not a valid upgrade.
        Course::factory()->create(['tier' => 'weekend', 'fee' => 50000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($standard);

        $this->actingAs($director)->get("/enrollments/{$enrollment->id}/upgrade-tier")->assertNotFound();
    }

    public function test_a_non_tiered_course_is_not_an_eligible_tier_upgrade_target_even_if_pricier(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        $pricierStandard = Course::factory()->create(['tier' => null, 'duration_weeks' => 8, 'fee' => 150000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($standard);

        $this->assertFalse($enrollment->eligibleTierUpgrades()->contains('id', $pricierStandard->id));

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade-tier", [
            'course_id' => $pricierStandard->id,
        ]);

        $response->assertSessionHasErrors('course_id');
        $this->assertSame($standard->id, $enrollment->fresh()->course_id);
    }

    public function test_a_course_the_student_already_holds_a_separate_enrollment_in_is_not_an_eligible_tier_upgrade_target(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        $vip = Course::factory()->create(['tier' => 'vip', 'fee' => 125000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($standard);
        $student->courses()->attach($vip->id, [
            'enrolled_at' => now(), 'status' => 'active', 'fee' => $vip->fee, 'original_fee' => $vip->fee,
        ]);

        $this->assertFalse($enrollment->eligibleTierUpgrades()->contains('id', $vip->id));

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade-tier", [
            'course_id' => $vip->id,
        ]);

        $response->assertSessionHasErrors('course_id');
    }

    public function test_upgrading_to_a_tier_charges_only_the_fee_difference_and_preserves_training_progress(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'course_type' => 'manual', 'schedule' => 'weekday', 'fee' => 95000, 'status' => 'active']);
        $vip = Course::factory()->create(['tier' => 'vip', 'course_type' => 'both', 'schedule' => 'weekday', 'duration_weeks' => 3, 'fee' => 125000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($standard);
        $this->attend($student, $standard, 5);
        Payment::create(['student_id' => $student->id, 'course_id' => $standard->id, 'amount' => 95000, 'payment_date' => now()->toDateString(), 'payment_method' => 'cash', 'status' => 'paid', 'recorded_by' => $director->id]);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade-tier", [
            'course_id' => $vip->id,
            'amount_paid' => 30000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");

        $enrollment->refresh();
        $this->assertSame($vip->id, $enrollment->course_id);
        $this->assertSame(125000.0, $enrollment->fee());
        $this->assertSame(125000.0, $enrollment->amountPaid());
        $this->assertSame(0.0, $enrollment->balance());

        $this->assertSame(5, $enrollment->attendedDays());
        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'course_id' => $vip->id]);
        $this->assertDatabaseMissing('attendances', ['course_id' => $standard->id]);

        $this->assertDatabaseHas('programme_upgrade_logs', [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'from_course_id' => $standard->id,
            'to_course_id' => $vip->id,
            'upgraded_by' => $director->id,
            'previous_fee' => 95000,
            'new_fee' => 125000,
            'amount_charged' => 30000,
        ]);
    }

    public function test_amount_paid_cannot_exceed_the_tier_upgrade_balance(): void
    {
        $director = User::factory()->director()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        $executive = Course::factory()->create(['tier' => 'executive', 'fee' => 155000, 'status' => 'active']);
        [, $enrollment] = $this->enrollStudent($standard);

        $response = $this->actingAs($director)->post("/enrollments/{$enrollment->id}/upgrade-tier", [
            'course_id' => $executive->id,
            'amount_paid' => 100000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('amount_paid');
        $this->assertSame($standard->id, $enrollment->fresh()->course_id);
    }

    public function test_the_student_page_shows_an_upgrade_tier_link_only_to_a_director(): void
    {
        $director = User::factory()->director()->create();
        $secretary = User::factory()->secretary()->create();
        $standard = Course::factory()->create(['duration_weeks' => 4, 'fee' => 95000, 'status' => 'active']);
        Course::factory()->create(['tier' => 'weekend', 'fee' => 125000, 'status' => 'active']);
        [$student, $enrollment] = $this->enrollStudent($standard);

        $directorResponse = $this->actingAs($director)->get("/students/{$student->id}");
        $directorResponse->assertOk();
        $directorResponse->assertSee(route('enrollments.upgrade-tier.create', $enrollment->id), false);

        $secretaryResponse = $this->actingAs($secretary)->get("/students/{$student->id}");
        $secretaryResponse->assertOk();
        $secretaryResponse->assertDontSee(route('enrollments.upgrade-tier.create', $enrollment->id), false);
    }
}
