<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'license_number',
        'course_type',
        'enrollment_date',
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
            'date_of_birth' => 'date',
            'enrollment_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // student_id_number is deliberately not fillable: it's a permanent,
        // system-assigned identifier (not something a form should be able to
        // set or change), derived from the auto-increment id so it's unique
        // without a collision-check loop. Tickets and certificates embed it
        // in their own numbers so every document for a student is traceable
        // back to the same id.
        static::created(function (Student $student) {
            $student->forceFill([
                'student_id_number' => 'CDS-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT),
            ])->save();
        });
    }

    /**
     * The courses this student is enrolled in.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->using(Enrollment::class)
            ->withPivot(['id', 'enrolled_at', 'due_date', 'status', 'locked_reason'])
            ->withTimestamps();
    }

    /**
     * The attendance records for this student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * The payment records for this student.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
