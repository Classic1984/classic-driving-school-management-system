<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CoursePriceListSeeder extends Seeder
{
    /**
     * Seed the Classic Driving School course price list. Safe to run more
     * than once — existing courses are matched by name and updated rather
     * than duplicated.
     *
     * Run with: php artisan db:seed --class=CoursePriceListSeeder
     */
    public function run(): void
    {
        $courses = [
            ['name' => 'Non-Experience (Auto & Manual) 4 Weeks', 'duration_weeks' => 4, 'duration_hours' => 80, 'fee' => 95000],
            ['name' => 'Non-Experience (Auto & Manual) 3 Weeks', 'duration_weeks' => 3, 'duration_hours' => 60, 'fee' => 85000],
            ['name' => 'Partial Experience (Auto & Manual)', 'duration_weeks' => 2, 'duration_hours' => 40, 'fee' => 75000],
            ['name' => 'Refreshing (Auto & Manual) 1 Week', 'duration_weeks' => 1, 'duration_hours' => 20, 'fee' => 65000],
            ['name' => 'Weekend Program (Auto & Manual) 4 Weekends', 'duration_weeks' => 4, 'duration_hours' => 32, 'fee' => 125000],
            ['name' => 'Weekend Program (Auto & Manual) 4 Weekends (AC)', 'duration_weeks' => 4, 'duration_hours' => 32, 'fee' => 155000],
            ['name' => 'Executive Program with AC 3 Weeks', 'duration_weeks' => 3, 'duration_hours' => 60, 'fee' => 155000],
            ['name' => 'Executive Training without AC 3 Weeks', 'duration_weeks' => 3, 'duration_hours' => 60, 'fee' => 125000],
            ['name' => 'VIP Program (Personal Car) 3 Weeks', 'duration_weeks' => 3, 'duration_hours' => 60, 'fee' => 125000],
            ['name' => "Learners' Permit Trainee", 'duration_weeks' => 1, 'duration_hours' => 2, 'fee' => 6000],
            ['name' => "Driver's License Trainee", 'duration_weeks' => 1, 'duration_hours' => 5, 'fee' => 50000],
            ['name' => 'School Certificate (Student)', 'duration_weeks' => 1, 'duration_hours' => 1, 'fee' => 1000],
            ['name' => "Online Certificate (Driver's License)", 'duration_weeks' => 1, 'duration_hours' => 1, 'fee' => 20000],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['name' => $course['name']],
                [
                    'course_type' => 'both',
                    'duration_weeks' => $course['duration_weeks'],
                    'duration_hours' => $course['duration_hours'],
                    'fee' => $course['fee'],
                    'status' => 'active',
                ]
            );
        }
    }
}
