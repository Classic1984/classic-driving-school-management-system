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
        ];
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
        return $this->belongsToMany(Student::class);
    }

    /**
     * The attendance records for this course.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
