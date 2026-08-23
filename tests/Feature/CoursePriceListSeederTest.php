<?php

namespace Tests\Feature;

use Database\Seeders\CoursePriceListSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoursePriceListSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_every_course_from_the_price_list(): void
    {
        $this->seed(CoursePriceListSeeder::class);

        $this->assertDatabaseCount('courses', 14);

        $this->assertDatabaseHas('courses', [
            'name' => 'Non-Experience (Auto & Manual) 4 Weeks',
            'fee' => 95000,
            'duration_weeks' => 4,
        ]);

        $this->assertDatabaseHas('courses', [
            'name' => "Learners' Permit Trainee",
            'fee' => 6000,
        ]);

        $this->assertDatabaseHas('courses', [
            'name' => 'Executive Training without AC 4 Weeks',
            'fee' => 155000,
            'duration_weeks' => 4,
        ]);
    }

    public function test_the_weekend_programs_are_seeded_with_a_weekend_schedule(): void
    {
        $this->seed(CoursePriceListSeeder::class);

        $this->assertDatabaseHas('courses', [
            'name' => 'Weekend Program (Auto & Manual) 4 Weekends',
            'schedule' => 'weekend',
        ]);
        $this->assertDatabaseHas('courses', [
            'name' => 'Weekend Program (Auto & Manual) 4 Weekends (AC)',
            'schedule' => 'weekend',
        ]);

        // Everything else on the price list is a weekday programme.
        $this->assertDatabaseHas('courses', [
            'name' => 'Non-Experience (Auto & Manual) 4 Weeks',
            'schedule' => 'weekday',
        ]);
    }

    public function test_the_weekend_program_schedule_fix_migration_corrects_a_mis_seeded_row(): void
    {
        // Regression test: the "Weekend Program" courses were originally
        // seeded before the `schedule` column existed and silently landed
        // on its `weekday` default. This is the one-time data-fix
        // migration that must correct that already-seeded row in place.
        DB::table('courses')->insert([
            'name' => 'Weekend Program (Auto & Manual) 4 Weekends',
            'course_type' => 'both',
            'schedule' => 'weekday',
            'duration_weeks' => 4,
            'duration_hours' => 32,
            'fee' => 125000,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (require database_path('migrations/2026_08_11_074646_fix_weekend_program_courses_schedule.php'))->up();

        $this->assertDatabaseHas('courses', [
            'name' => 'Weekend Program (Auto & Manual) 4 Weekends',
            'schedule' => 'weekend',
        ]);
    }

    public function test_running_it_twice_does_not_duplicate_courses(): void
    {
        $this->seed(CoursePriceListSeeder::class);
        $this->seed(CoursePriceListSeeder::class);

        $this->assertDatabaseCount('courses', 14);
    }
}
