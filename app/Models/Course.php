<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'course_type',
        'duration_hours',
        'duration_weeks',
        'fee',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'duration_hours' => 'integer',
            'duration_weeks' => 'integer',
        ];
    }

    /**
     * The number of days a student has to clear their balance before this
     * course's enrollment is locked: 4 days for 3-4 week courses, 2 days
     * for 1-2 week courses.
     */
    public function gracePeriodDays(): int
    {
        return $this->duration_weeks >= 3 ? 4 : 2;
    }

    /**
     * The instructors assigned to teach this course.
     */
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class);
    }

    /**
     * The students enrolled in this course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->using(Enrollment::class)
            ->withPivot(['enrolled_at', 'due_date', 'status', 'locked_reason'])
            ->withTimestamps();
    }

    /**
     * The attendance records for this course.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * The payment records for this course.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
