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
        'schedule',
        'duration_hours',
        'duration_weeks',
        'fee',
        'online_certificate_fee',
        'student_certificate_fee',
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
            'online_certificate_fee' => 'decimal:2',
            'student_certificate_fee' => 'decimal:2',
            'duration_hours' => 'integer',
            'duration_weeks' => 'integer',
        ];
    }

    public function isWeekend(): bool
    {
        return $this->schedule === 'weekend';
    }

    /**
     * The number of days a student has to clear their balance before this
     * course's enrollment is locked. Weekend-schedule courses get a full
     * week (7 days), since a weekend-only student is next back at the
     * school the following Saturday, not mid-week. Weekday courses keep
     * the shorter 4-day (3-4 week courses) or 2-day (1-2 week courses)
     * grace period.
     */
    public function gracePeriodDays(): int
    {
        if ($this->isWeekend()) {
            return 7;
        }

        return $this->duration_weeks >= 3 ? 4 : 2;
    }

    /**
     * The total number of one-hour training sessions a student must attend
     * to complete this course, per the school's attendance policy: 5
     * training days per week (e.g. a 4-week program is 20 training days).
     */
    public function totalTrainingDays(): int
    {
        return $this->duration_weeks * 5;
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
            ->withPivot(['id', 'enrolled_at', 'due_date', 'status', 'locked_reason', 'fee', 'original_fee', 'discount_percentage', 'discount_amount', 'discount_reason', 'discount_reason_note', 'discount_approved_by', 'reactivated_at', 'reactivation_fee', 'reactivated_by', 'online_certificate_fee', 'student_certificate_fee'])
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
