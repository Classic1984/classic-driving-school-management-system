<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DiscountAuditLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    protected function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ], $overrides);
    }

    public function test_secretary_can_apply_a_preset_discount_within_their_limit(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
        ]));

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $enrollment = $student->courses->first()->pivot;

        $this->assertSame(95000.0, $enrollment->originalFee());
        $this->assertSame(5000.0, (float) $enrollment->discount_amount);
        $this->assertSame(90000.0, $enrollment->fee());
        $this->assertSame($secretary->id, $enrollment->discount_approved_by);

        $this->assertDatabaseHas('discount_audit_logs', [
            'student_id' => $student->id,
            'applied_by' => $secretary->id,
            'discount_amount' => 5000,
            'reason' => 'promotional_offer',
        ]);
    }

    public function test_secretary_cannot_apply_a_discount_beyond_their_preset_limit(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '10000',
            'discount_reason' => 'promotional_offer',
        ]));

        $response->assertSessionHasErrors('discount_choice');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_secretary_cannot_use_a_custom_discount(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => 'custom',
            'custom_discount_percentage' => 12,
            'discount_reason' => 'promotional_offer',
        ]));

        $response->assertSessionHasErrors('discount_choice');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_director_can_apply_a_preset_discount_beyond_the_secretary_limit(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '10000',
            'discount_reason' => 'directors_approval',
        ]));

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $this->assertSame(10000.0, (float) $student->courses->first()->pivot->discount_amount);
        $this->assertSame(85000.0, $student->courses->first()->pivot->fee());
    }

    public function test_director_can_apply_a_custom_percentage_discount(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => 'custom',
            'custom_discount_percentage' => 12,
            'discount_reason' => 'staff_relative',
        ]));

        $response->assertSessionHasNoErrors();

        $enrollment = Student::where('email', 'jane.doe@example.com')->firstOrFail()->courses->first()->pivot;
        $this->assertSame(12.0, (float) $enrollment->discount_percentage);
        $this->assertSame(11400.0, (float) $enrollment->discount_amount);
        $this->assertSame(83600.0, $enrollment->fee());
    }

    public function test_director_can_apply_a_custom_fixed_amount_discount(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => 'custom',
            'custom_discount_amount' => 15000,
            'discount_reason' => 'corporate_client',
        ]));

        $response->assertSessionHasNoErrors();

        $enrollment = Student::where('email', 'jane.doe@example.com')->firstOrFail()->courses->first()->pivot;
        $this->assertSame(15000.0, (float) $enrollment->discount_amount);
        $this->assertSame(80000.0, $enrollment->fee());
    }

    public function test_custom_discount_rejects_both_percentage_and_amount_together(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => 'custom',
            'custom_discount_percentage' => 10,
            'custom_discount_amount' => 15000,
            'discount_reason' => 'other',
            'discount_reason_note' => 'Test',
        ]));

        $response->assertSessionHasErrors('custom_discount_percentage');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_discount_requires_a_reason(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
        ]));

        $response->assertSessionHasErrors('discount_reason');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_discount_reason_other_requires_a_note(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'other',
        ]));

        $response->assertSessionHasErrors('discount_reason_note');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_registering_without_a_discount_leaves_the_final_fee_equal_to_the_original(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($user)->post('/students', $this->registrationData([
            'course_id' => $course->id,
        ]));

        $response->assertSessionHasNoErrors();

        $enrollment = Student::where('email', 'jane.doe@example.com')->firstOrFail()->courses->first()->pivot;
        $this->assertFalse($enrollment->hasDiscount());
        $this->assertSame(95000.0, $enrollment->fee());
        $this->assertSame(95000.0, $enrollment->originalFee());
        $this->assertDatabaseCount('discount_audit_logs', 0);
    }

    public function test_payments_and_balance_use_the_discounted_final_fee(): void
    {
        $secretary = User::factory()->secretary()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($secretary)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '5000',
            'discount_reason' => 'promotional_offer',
            'amount_paid' => 40000,
            'payment_method' => 'cash',
        ]))->assertSessionHasNoErrors();

        $student = Student::where('email', 'jane.doe@example.com')->firstOrFail();
        $enrollment = $student->courses->first()->pivot;

        // 95,000 - 5,000 discount = 90,000 final fee; 40,000 paid; 50,000 balance.
        $this->assertSame(90000.0, $enrollment->fee());
        $this->assertSame(50000.0, $enrollment->balance());
    }

    public function test_a_preset_discount_cannot_exceed_the_course_fee(): void
    {
        $director = User::factory()->director()->create();
        $course = Course::factory()->create(['fee' => 8000]);

        $response = $this->actingAs($director)->post('/students', $this->registrationData([
            'course_id' => $course->id,
            'discount_choice' => '10000',
            'discount_reason' => 'directors_approval',
        ]));

        $response->assertSessionHasNoErrors();

        $enrollment = Student::where('email', 'jane.doe@example.com')->firstOrFail()->courses->first()->pivot;
        $this->assertSame(8000.0, (float) $enrollment->discount_amount);
        $this->assertSame(0.0, $enrollment->fee());
    }
}
