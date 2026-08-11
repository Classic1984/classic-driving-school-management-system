<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentService>
 */
class StudentServiceFactory extends Factory
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
            'service_id' => Service::factory(),
            'price' => fake()->randomFloat(2, 1000, 50000),
        ];
    }
}
