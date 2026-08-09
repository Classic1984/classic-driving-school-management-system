<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_student_routes(): void
    {
        $student = Student::factory()->create();

        $this->get('/students')->assertRedirect('/login');
        $this->get('/students/create')->assertRedirect('/login');
        $this->get("/students/{$student->id}")->assertRedirect('/login');
        $this->get("/students/{$student->id}/edit")->assertRedirect('/login');
        $this->post('/students', [])->assertRedirect('/login');
        $this->put("/students/{$student->id}", [])->assertRedirect('/login');
        $this->delete("/students/{$student->id}")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_student_index(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);

        $response = $this->actingAs($user)->get('/students');

        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_student_index_shows_the_student_id_number(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Jane Doe']);

        $response = $this->actingAs($user)->get('/students');

        $response->assertOk();
        $response->assertSee($student->student_id_number);
    }

    public function test_student_index_can_be_filtered_by_search_term(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        Student::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']);

        $response = $this->actingAs($user)->get('/students?search=Jane');

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertDontSee('John Smith');
    }

    public function test_student_index_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Student::factory()->create(['name' => 'Active Student', 'status' => 'active']);
        Student::factory()->create(['name' => 'Withdrawn Student', 'status' => 'withdrawn']);

        $response = $this->actingAs($user)->get('/students?status=withdrawn');

        $response->assertOk();
        $response->assertSee('Withdrawn Student');
        $response->assertDontSee('Active Student');
    }

    public function test_student_index_can_be_filtered_by_course(): void
    {
        $user = User::factory()->create();
        $courseA = Course::factory()->create(['name' => 'Course A']);
        $courseB = Course::factory()->create(['name' => 'Course B']);
        $studentA = Student::factory()->create(['name' => 'In Course A']);
        $studentB = Student::factory()->create(['name' => 'In Course B']);
        $studentA->courses()->attach($courseA->id, ['enrolled_at' => now(), 'status' => 'active']);
        $studentB->courses()->attach($courseB->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get("/students?course_id={$courseA->id}");

        $response->assertOk();
        $response->assertSee('In Course A');
        $response->assertDontSee('In Course B');
    }

    public function test_student_index_can_be_filtered_by_payment_lock_status(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lockedStudent = Student::factory()->create(['name' => 'Locked Student']);
        $clearStudent = Student::factory()->create(['name' => 'Clear Student']);
        $lockedStudent->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'locked', 'locked_reason' => 'overdue_balance']);
        $clearStudent->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get('/students?payment=locked');

        $response->assertOk();
        $response->assertSee('Locked Student');
        $response->assertDontSee('Clear Student');
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/students/create');

        $response->assertOk();
    }

    public function test_authenticated_user_can_store_a_student(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $data = [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-0100',
            'address' => '123 Main St',
            'date_of_birth' => '2000-01-15',
            'license_number' => 'LIC-12345',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
            'amount_paid' => 50000,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
        ]);

        $student = Student::where('email', 'john.smith@example.com')->firstOrFail();
        $this->assertMatchesRegularExpression('/^CDS-\d{5}$/', $student->student_id_number);
        $response->assertRedirect("/students/{$student->id}");

        $this->assertTrue($student->courses->contains($course->id));
        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 50000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $enrollment = $student->courses->firstWhere('id', $course->id);
        $this->assertSame(45000.0, $enrollment->pivot->balance());
    }

    public function test_registering_a_student_always_sets_status_to_active_automatically(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'Auto Active Student',
            'email' => 'auto.active@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'course_id' => $course->id,
        ]);

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'auto.active@example.com')->firstOrFail();
        $this->assertSame('active', $student->status);
    }

    public function test_the_registration_form_does_not_offer_a_manual_status_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/students/create');

        $response->assertOk();
        $response->assertDontSee('name="status"', false);
    }

    public function test_registering_into_a_weekend_course_gives_a_seven_day_payment_grace_period(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000, 'schedule' => 'weekend']);

        $data = [
            'name' => 'Weekend Student',
            'email' => 'weekend.student@example.com',
            'phone' => '555-0100',
            'address' => '123 Main St',
            'date_of_birth' => '2000-01-15',
            'license_number' => 'LIC-99999',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
            'amount_paid' => 47500,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'weekend.student@example.com')->firstOrFail();
        $enrollment = $student->courses->firstWhere('id', $course->id);

        $this->assertSame(now()->addDays(7)->toDateString(), $enrollment->pivot->due_date->toDateString());

        // Balance is still owed a week later: the enrollment should now be overdue and locked.
        $this->travel(8)->days();
        $enrollment->pivot->refreshStatus();

        $this->assertTrue($enrollment->pivot->fresh()->isOverdue());
        $this->assertSame('locked', $enrollment->pivot->fresh()->status);
        $this->assertSame('overdue_balance', $enrollment->pivot->fresh()->locked_reason);
    }

    public function test_starting_double_period_immediately_shortens_the_payment_grace_period_to_two_days(): void
    {
        $user = User::factory()->create();
        // A 4-week weekday course would normally get a 4-day grace period.
        $course = Course::factory()->create(['fee' => 95000, 'schedule' => 'weekday', 'duration_weeks' => 4]);

        $data = [
            'name' => 'Double Period Student',
            'email' => 'double.period@example.com',
            'phone' => '555-0100',
            'address' => '123 Main St',
            'date_of_birth' => '2000-01-15',
            'license_number' => 'LIC-88888',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
            'starts_double_period' => '1',
            'amount_paid' => 47500,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'double.period@example.com')->firstOrFail();
        $enrollment = $student->courses->firstWhere('id', $course->id);

        $this->assertSame(now()->addDays(2)->toDateString(), $enrollment->pivot->due_date->toDateString());
    }

    public function test_starting_double_period_has_no_effect_on_a_weekend_courses_grace_period(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000, 'schedule' => 'weekend']);

        $data = [
            'name' => 'Weekend Double Period Student',
            'email' => 'weekend.double.period@example.com',
            'phone' => '555-0100',
            'address' => '123 Main St',
            'date_of_birth' => '2000-01-15',
            'license_number' => 'LIC-77777',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
            'starts_double_period' => '1',
            'amount_paid' => 47500,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'weekend.double.period@example.com')->firstOrFail();
        $enrollment = $student->courses->firstWhere('id', $course->id);

        // Still the weekend course's normal 7-day grace period, not the 2-day double-period rule.
        $this->assertSame(now()->addDays(7)->toDateString(), $enrollment->pivot->due_date->toDateString());
    }

    public function test_registering_a_student_without_an_initial_payment_still_creates_the_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $data = [
            'name' => 'Grace Okoro',
            'email' => 'grace.okoro@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'grace.okoro@example.com')->firstOrFail();
        $this->assertTrue($student->courses->contains($course->id));
        $this->assertDatabaseCount('payments', 0);

        $enrollment = $student->courses->firstWhere('id', $course->id);
        $this->assertSame(95000.0, $enrollment->pivot->balance());
    }

    public function test_registering_a_student_locks_in_the_courses_current_fee(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $this->actingAs($user)->post('/students', [
            'name' => 'Early Bird',
            'email' => 'early.bird@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
        ])->assertSessionHasNoErrors();

        // The school raises the course's price after the first student enrolled.
        $course->update(['fee' => 120000]);

        $this->actingAs($user)->post('/students', [
            'name' => 'Late Comer',
            'email' => 'late.comer@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
        ])->assertSessionHasNoErrors();

        $earlyBird = Student::where('email', 'early.bird@example.com')->firstOrFail();
        $lateComer = Student::where('email', 'late.comer@example.com')->firstOrFail();

        $this->assertSame(95000.0, $earlyBird->courses->first()->pivot->fee());
        $this->assertSame(120000.0, $lateComer->courses->first()->pivot->fee());
    }

    public function test_storing_a_student_rejects_an_enrollment_date_other_than_today(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->addDay()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
        ]);

        $response->assertSessionHasErrors('enrollment_date');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_storing_a_student_requires_a_course(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('course_id');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_storing_a_student_requires_a_payment_method_when_an_amount_is_paid(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'course_id' => $course->id,
            'amount_paid' => 50000,
        ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_authenticated_user_can_store_a_student_with_the_full_registration_form_fields(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $course = Course::factory()->create(['fee' => 95000]);

        $data = [
            'course_id' => $course->id,
            'name' => 'Amaka Obi',
            'email' => 'amaka@example.com',
            'phone' => '555-0100',
            'address' => '123 Main St',
            'date_of_birth' => '2000-01-15',
            'mother_maiden_name' => 'Chidinma Eze',
            'sex' => 'female',
            'state_of_origin' => 'Rivers',
            'local_government_area' => 'Port Harcourt',
            'occupation' => 'business',
            'next_of_kin_name' => 'Chinedu Obi',
            'next_of_kin_address' => '456 Kin St',
            'next_of_kin_phone' => '555-0199',
            'next_of_kin_email' => 'chinedu@example.com',
            'course_type' => 'manual',
            'vehicle_class' => 'light',
            'has_driving_experience' => '0',
            'wears_glasses' => '1',
            'referral_source' => 'other',
            'referral_source_other' => 'Word of mouth',
            'photo' => UploadedFile::fake()->image('passport.jpg'),
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasNoErrors();

        $student = Student::where('email', 'amaka@example.com')->firstOrFail();
        $response->assertRedirect("/students/{$student->id}");
        $this->assertSame('Chidinma Eze', $student->mother_maiden_name);
        $this->assertSame('female', $student->sex);
        $this->assertSame('Rivers', $student->state_of_origin);
        $this->assertSame('Port Harcourt', $student->local_government_area);
        $this->assertSame('business', $student->occupation);
        $this->assertSame('Chinedu Obi', $student->next_of_kin_name);
        $this->assertSame('456 Kin St', $student->next_of_kin_address);
        $this->assertSame('555-0199', $student->next_of_kin_phone);
        $this->assertSame('chinedu@example.com', $student->next_of_kin_email);
        $this->assertSame('light', $student->vehicle_class);
        $this->assertFalse($student->has_driving_experience);
        $this->assertTrue($student->wears_glasses);
        $this->assertSame('other', $student->referral_source);
        $this->assertSame('Word of mouth', $student->referral_source_other);
        $this->assertNotNull($student->photo_path);
        Storage::disk('public')->assertExists($student->photo_path);
    }

    public function test_updating_a_student_with_a_new_photo_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $student = Student::factory()->create(['photo_path' => 'student-photos/old.jpg']);
        Storage::disk('public')->put('student-photos/old.jpg', 'old-contents');

        $response = $this->actingAs($user)->put("/students/{$student->id}", [
            'name' => $student->name,
            'email' => $student->email,
            'phone' => $student->phone,
            'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
            'course_type' => $student->course_type,
            'enrollment_date' => $student->enrollment_date->format('Y-m-d'),
            'status' => $student->status,
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('student-photos/old.jpg');
        $newPath = $student->fresh()->photo_path;
        $this->assertNotSame('student-photos/old.jpg', $newPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_storing_a_student_rejects_a_local_government_area_that_does_not_belong_to_the_state(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Bola Ade',
            'email' => 'bola@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'state_of_origin' => 'Rivers',
            'local_government_area' => 'Ikeja',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasErrors('local_government_area');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_storing_a_student_rejects_an_unrecognized_state_of_origin(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Bola Ade',
            'email' => 'bola@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'state_of_origin' => 'Atlantis',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($user)->post('/students', $data);

        $response->assertSessionHasErrors('state_of_origin');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_each_student_gets_a_unique_sequential_id_number(): void
    {
        $first = Student::factory()->create();
        $second = Student::factory()->create();

        $this->assertNotSame($first->student_id_number, $second->student_id_number);
        $this->assertSame("CDS-{$this->pad($first->id)}", $first->student_id_number);
        $this->assertSame("CDS-{$this->pad($second->id)}", $second->student_id_number);
    }

    protected function pad(int $id): string
    {
        return str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    public function test_storing_a_student_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/students', [
            'name' => '',
            'email' => 'not-an-email',
            'phone' => '',
            'date_of_birth' => '',
            'course_type' => 'invalid-course',
            'enrollment_date' => '',
        ]);

        $response->assertSessionHasErrors([
            'name', 'email', 'phone', 'date_of_birth', 'course_type', 'enrollment_date', 'course_id',
        ]);

        $this->assertDatabaseCount('students', 0);
    }

    public function test_storing_a_student_requires_unique_email(): void
    {
        $user = User::factory()->create();
        $existing = Student::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'New Student',
            'email' => 'duplicate@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '2000-01-15',
            'course_type' => 'manual',
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('students', 1);
    }

    public function test_authenticated_user_can_view_a_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee($student->name);
    }

    public function test_student_page_shows_enrolled_courses(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Defensive Driving 101']);
        $student->courses()->attach($course);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee('Defensive Driving 101');
    }

    public function test_student_page_shows_the_payment_summary(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 1000]);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);
        Payment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id, 'amount' => 400, 'status' => 'paid']);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        // Fees 1000, paid 400, balance 600.
        $response->assertSeeInOrder(['Total Fees', '1,000.00', 'Total Paid', '400.00', 'Balance', '600.00']);
    }

    public function test_student_page_offers_a_quick_payment_form_when_enrolled(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Defensive Driving 101']);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertSee(route('payments.store'), false);
        $response->assertSee('Defensive Driving 101');
    }

    public function test_recording_a_payment_from_the_student_page_redirects_back_to_the_profile(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create(['fee' => 500]);
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        $response = $this->actingAs($user)->post('/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 200,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
            'redirect_to_student' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect("/students/{$student->id}");
        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'amount' => 200]);
    }

    public function test_authenticated_user_can_view_edit_form(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->get("/students/{$student->id}/edit");

        $response->assertOk();
        $response->assertSee($student->name);
    }

    public function test_authenticated_user_can_update_a_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put("/students/{$student->id}", [
            'name' => 'New Name',
            'email' => $student->email,
            'phone' => $student->phone,
            'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
            'license_number' => $student->license_number,
            'course_type' => $student->course_type,
            'enrollment_date' => $student->enrollment_date->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/students');

        $this->assertSame('New Name', $student->fresh()->name);
        $this->assertSame('completed', $student->fresh()->status);
    }

    public function test_updating_a_student_rejects_a_future_enrollment_date(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->put("/students/{$student->id}", [
            'name' => $student->name,
            'email' => $student->email,
            'phone' => $student->phone,
            'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
            'course_type' => $student->course_type,
            'enrollment_date' => now()->addDay()->toDateString(),
            'status' => $student->status,
        ]);

        $response->assertSessionHasErrors('enrollment_date');
        $this->assertNotSame(now()->addDay()->toDateString(), $student->fresh()->enrollment_date->toDateString());
    }

    public function test_authenticated_user_can_delete_a_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->delete("/students/{$student->id}");

        $response->assertRedirect('/students');
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
