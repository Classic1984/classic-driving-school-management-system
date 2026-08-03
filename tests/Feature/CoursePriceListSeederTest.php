<?php

namespace Tests\Feature;

use App\Models\Course;
use Database\Seeders\CoursePriceListSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursePriceListSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_every_course_from_the_price_list(): void
    {
        $this->seed(CoursePriceListSeeder::class);

        $this->assertDatabaseCount('courses', 13);

        $this->assertDatabaseHas('courses', [
            'name' => 'Non-Experience (Auto & Manual) 4 Weeks',
            'fee' => 95000,
            'duration_weeks' => 4,
        ]);

        $this->assertDatabaseHas('courses', [
            'name' => "Learners' Permit Trainee",
            'fee' => 6000,
        ]);
    }

    public function test_running_it_twice_does_not_duplicate_courses(): void
    {
        $this->seed(CoursePriceListSeeder::class);
        $this->seed(CoursePriceListSeeder::class);

        $this->assertDatabaseCount('courses', 13);
    }
}
