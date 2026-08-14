<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'instructor_id' => Instructor::factory(),
            'vehicle_id' => Vehicle::factory(),
            'date' => fake()->date(),
            'status' => fake()->randomElement(['present', 'absent', 'late', 'excused']),
            'type' => fake()->optional()->randomElement(['practical', 'classroom']),
            'duration' => fake()->optional()->numberBetween(1, 3),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
