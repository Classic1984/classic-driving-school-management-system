<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\TheoryClass;
use App\Models\TheoryClassAttendance;
use App\Models\TheoryClassCancellation;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TheoryClassTest extends TestCase
{
    use RefreshDatabase;

    protected function activeStudent(): Student
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $student->courses()->attach($course->id, ['enrolled_at' => now(), 'status' => 'active']);

        return $student;
    }

    public function test_the_index_page_lists_theory_classes(): void
    {
        $user = User::factory()->create();
        $theoryClass = TheoryClass::factory()->create(['topic' => 'Road Signs']);

        $response = $this->actingAs($user)->get(route('theory-classes.index'));

        $response->assertOk();
        $response->assertSee('Road Signs');
    }

    public function test_the_roster_page_lists_every_actively_enrolled_student(): void
    {
        $user = User::factory()->create();
        $student = $this->activeStudent();
        $theoryClass = TheoryClass::factory()->create();

        $response = $this->actingAs($user)->get(route('theory-classes.show', $theoryClass));

        $response->assertOk();
        $response->assertSee($student->name);
        $response->assertSee('Not yet marked');
    }

    public function test_a_course_manager_can_mark_a_student_present(): void
    {
        $user = User::factory()->create();
        $student = $this->activeStudent();
        $theoryClass = TheoryClass::factory()->create();

        $response = $this->actingAs($user)->post(route('theory-classes.attendances.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'present',
            'score' => 85,
            'remarks' => 'Good grasp of the topic.',
        ]);

        $response->assertRedirect(route('theory-classes.show', $theoryClass));
        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'present',
            'score' => 85,
            'remarks' => 'Good grasp of the topic.',
            'marked_by' => $user->id,
        ]);
    }

    public function test_marking_a_student_again_updates_the_same_record_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $student = $this->activeStudent();
        $theoryClass = TheoryClass::factory()->create();

        $this->actingAs($user)->post(route('theory-classes.attendances.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'present',
        ]);
        $this->actingAs($user)->post(route('theory-classes.attendances.store', $theoryClass), [
            'student_id' => $student->id,
            'status' => 'late',
            'score' => 70,
        ]);

        $this->assertDatabaseCount('theory_class_attendances', 1);
        $this->assertDatabaseHas('theory_class_attendances', [
            'theory_class_id' => $theoryClass->id,
            'student_id' => $student->id,
            'status' => 'late',
            'score' => 70,
        ]);
    }

    public function test_a_course_manager_can_update_the_class_details(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $theoryClass = TheoryClass::factory()->create(['topic' => 'Old Topic']);

        $response = $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'topic' => 'Right of Way',
            'instructor_id' => $instructor->id,
            'start_time' => '11:00',
            'notes' => 'Extra Q&A session.',
        ]);

        $response->assertRedirect(route('theory-classes.show', $theoryClass));
        $this->assertDatabaseHas('theory_classes', [
            'id' => $theoryClass->id,
            'topic' => 'Right of Way',
            'instructor_id' => $instructor->id,
            'notes' => 'Extra Q&A session.',
        ]);
    }

    public function test_assigning_an_instructor_with_app_access_pushes_them(): void
    {
        $user = User::factory()->create();
        $instructorUser = User::factory()->create(['role' => 'instructor']);
        $instructor = Instructor::factory()->create();
        $instructor->forceFill(['user_id' => $instructorUser->id])->save();
        $theoryClass = TheoryClass::factory()->create(['instructor_id' => null, 'class_date' => now()->addDay()]);

        $this->mock(WebPushService::class, function ($mock) use ($instructorUser) {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(fn ($user, $title, $body, $url) => $user->is($instructorUser)
                    && $title === 'Theory Class Assigned'
                    && $url === route('instructor.dashboard'));
        });

        $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), ['instructor_id' => $instructor->id]);
    }

    public function test_assigning_an_instructor_without_app_access_does_not_push(): void
    {
        $user = User::factory()->create();
        $instructor = Instructor::factory()->create();
        $theoryClass = TheoryClass::factory()->create(['instructor_id' => null]);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldNotReceive('sendToUser');
        });

        $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), ['instructor_id' => $instructor->id]);
    }

    public function test_resaving_the_same_instructor_does_not_push_again(): void
    {
        $user = User::factory()->create();
        $instructorUser = User::factory()->create(['role' => 'instructor']);
        $instructor = Instructor::factory()->create();
        $instructor->forceFill(['user_id' => $instructorUser->id])->save();
        $theoryClass = TheoryClass::factory()->create(['instructor_id' => $instructor->id]);

        $this->mock(WebPushService::class, function ($mock) {
            $mock->shouldNotReceive('sendToUser');
        });

        $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'instructor_id' => $instructor->id,
            'notes' => 'Just updating notes, same instructor.',
        ]);
    }

    public function test_resaving_details_after_start_time_was_already_set_does_not_fail_validation(): void
    {
        $user = User::factory()->create();
        // Mirrors how the database round-trips a TIME column - a fresh
        // insert can come back with seconds even though only "H:i" was
        // ever submitted, which is exactly what broke resubmitting the
        // form unchanged.
        $theoryClass = TheoryClass::factory()->create(['start_time' => '10:00:00']);

        $response = $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'topic' => 'Right of Way',
            'start_time' => $theoryClass->start_time,
        ]);

        $response->assertRedirect(route('theory-classes.show', $theoryClass));
        $response->assertSessionDoesntHaveErrors('start_time');
        $this->assertSame('10:00', $theoryClass->refresh()->start_time);
    }

    public function test_a_course_manager_can_upload_lecture_material(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $theoryClass = TheoryClass::factory()->create();
        $file = UploadedFile::fake()->create('road-signs.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'materials' => $file,
        ]);

        $response->assertRedirect(route('theory-classes.show', $theoryClass));
        $theoryClass->refresh();
        $this->assertNotNull($theoryClass->materials_path);
        $this->assertSame('road-signs.pdf', $theoryClass->materials_original_name);
        Storage::disk('public')->assertExists($theoryClass->materials_path);
    }

    public function test_uploading_new_material_replaces_and_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $theoryClass = TheoryClass::factory()->create();

        $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'materials' => UploadedFile::fake()->create('first.pdf', 100, 'application/pdf'),
        ]);
        $oldPath = $theoryClass->refresh()->materials_path;

        $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'materials' => UploadedFile::fake()->create('second.pdf', 100, 'application/pdf'),
        ]);
        $theoryClass->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($theoryClass->materials_path);
        $this->assertSame('second.pdf', $theoryClass->materials_original_name);
    }

    public function test_uploading_material_of_a_disallowed_type_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $theoryClass = TheoryClass::factory()->create();
        $file = UploadedFile::fake()->create('lecture.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($user)->patch(route('theory-classes.update', $theoryClass), [
            'materials' => $file,
        ]);

        $response->assertSessionHasErrors('materials');
        $this->assertNull($theoryClass->refresh()->materials_path);
    }

    public function test_the_roster_page_shows_a_download_link_when_material_is_uploaded(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $theoryClass = TheoryClass::factory()->create([
            'materials_path' => 'theory-class-materials/example.pdf',
            'materials_original_name' => 'Road Signs Slides.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('theory-classes.show', $theoryClass));

        $response->assertOk();
        $response->assertSee('Road Signs Slides.pdf');
    }

    public function test_attendance_percentage_and_counts_reflect_the_roster(): void
    {
        $theoryClass = TheoryClass::factory()->create();
        $present = $this->activeStudent();
        $absent = $this->activeStudent();

        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $theoryClass->id,
            'student_id' => $present->id,
            'status' => 'present',
        ]);
        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $theoryClass->id,
            'student_id' => $absent->id,
            'status' => 'absent',
        ]);
        $theoryClass->load('attendances');

        $this->assertSame(2, $theoryClass->expectedCount());
        $this->assertSame(1, $theoryClass->presentCount());
        $this->assertSame(1, $theoryClass->absentCount());
        $this->assertSame(50, $theoryClass->attendancePercentage());
    }

    public function test_the_index_page_prompts_a_course_manager_to_create_todays_class_when_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('theory-classes.index'));

        $response->assertOk();
        $response->assertSee("Create Today's Class");
    }

    public function test_the_index_page_does_not_prompt_when_todays_class_already_exists(): void
    {
        $user = User::factory()->create();
        TheoryClass::factory()->create(['class_date' => today()]);

        $response = $this->actingAs($user)->get(route('theory-classes.index'));

        $response->assertOk();
        $response->assertDontSee("Create Today's Class");
    }

    public function test_the_index_page_does_not_prompt_a_non_course_manager(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('theory-classes.index'));

        $response->assertOk();
        $response->assertDontSee("Create Today's Class");
    }

    public function test_a_course_manager_can_manually_create_todays_class(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('theory-classes.create-today'));

        $theoryClass = TheoryClass::whereDate('class_date', today())->firstOrFail();
        $response->assertRedirect(route('theory-classes.show', $theoryClass));
        $this->assertDatabaseHas('theory_classes', ['class_date' => today()->toDateString()]);
    }

    public function test_manually_creating_todays_class_twice_does_not_duplicate_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('theory-classes.create-today'));
        $this->actingAs($user)->post(route('theory-classes.create-today'));

        $this->assertDatabaseCount('theory_classes', 1);
    }

    public function test_a_non_course_manager_cannot_manually_create_todays_class(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('theory-classes.create-today'));

        $response->assertForbidden();
        $this->assertDatabaseCount('theory_classes', 0);
    }

    public function test_manually_creating_todays_class_is_blocked_when_today_is_cancelled(): void
    {
        $user = User::factory()->create();
        TheoryClassCancellation::factory()->create([
            'class_date' => today(),
            'cancelled_by' => User::factory()->create()->id,
        ]);

        $response = $this->actingAs($user)->post(route('theory-classes.create-today'));

        $response->assertRedirect(route('theory-classes.index'));
        $this->assertDatabaseCount('theory_classes', 0);
    }

    public function test_student_theory_progress_summarizes_their_attendance_and_scores(): void
    {
        $student = Student::factory()->create(['enrollment_date' => today()->subWeeks(3)]);

        $attended = TheoryClass::factory()->create(['class_date' => today()->subWeeks(2), 'topic' => 'Road Signs']);
        $missed = TheoryClass::factory()->create(['class_date' => today()->subWeek(), 'topic' => 'Right of Way']);

        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $attended->id,
            'student_id' => $student->id,
            'status' => 'present',
            'score' => 90,
        ]);
        TheoryClassAttendance::factory()->create([
            'theory_class_id' => $missed->id,
            'student_id' => $student->id,
            'status' => 'absent',
        ]);

        $progress = $student->theoryProgress();

        $this->assertSame(1, $progress['classes_attended']);
        $this->assertSame(2, $progress['classes_expected']);
        $this->assertSame(50, $progress['attendance_percentage']);
        $this->assertSame(1, $progress['topics_completed']);
        $this->assertSame(90, $progress['average_score']);
        $this->assertSame(['Right of Way'], $progress['outstanding_topics']->all());
    }
}
