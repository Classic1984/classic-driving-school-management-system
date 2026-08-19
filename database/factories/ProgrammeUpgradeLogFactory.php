<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\ProgrammeUpgradeLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgrammeUpgradeLog>
 */
class ProgrammeUpgradeLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $previousFee = fake()->randomFloat(2, 40000, 70000);
        $newFee = $previousFee + fake()->randomFloat(2, 10000, 40000);

        $student = Student::factory()->create();
        $fromCourse = Course::factory()->create(['duration_weeks' => 2, 'fee' => $previousFee]);
        $toCourse = Course::factory()->create(['duration_weeks' => 4, 'fee' => $newFee]);
        $student->courses()->attach($fromCourse->id, ['enrolled_at' => now(), 'status' => 'active', 'fee' => $newFee]);
        $enrollment = $student->courses()->where('course_id', $fromCourse->id)->first()->pivot;

        return [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'from_course_id' => $fromCourse->id,
            'to_course_id' => $toCourse->id,
            'upgraded_by' => User::factory()->director(),
            'attended_days_at_upgrade' => fake()->numberBetween(0, 5),
            'previous_fee' => $previousFee,
            'new_fee' => $newFee,
            'amount_charged' => $newFee - $previousFee,
        ];
    }
}
