<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => 'TCK-'.fake()->unique()->numerify('######'),
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'instructor_id' => Instructor::factory(),
            'date' => fake()->date(),
            'vehicle' => fake()->randomElement(['Toyota Corolla', 'Honda Civic', 'Nissan Sentra']),
            'lesson_number' => fake()->numberBetween(1, 20),
            'payment_status' => 'cleared',
        ];
    }
}
