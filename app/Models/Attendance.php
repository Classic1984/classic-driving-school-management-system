<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'instructor_id',
        'vehicle_id',
        'date',
        'status',
        'type',
        'duration',
        'notes',
        'logged_by',
    ];

    /**
     * Morning/Afternoon/Evening, derived from when this record was actually
     * saved (created_at) rather than a separately entered field - the
     * training login is logged in real time, so the save timestamp already
     * is the time of day the training happened.
     */
    public function sessionPeriod(): ?string
    {
        if ($this->created_at === null) {
            return null;
        }

        return match (true) {
            $this->created_at->hour < 12 => 'Morning',
            $this->created_at->hour < 17 => 'Afternoon',
            default => 'Evening',
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * The staff member who saved this training login. Deliberately not
     * mass-assignable: always set server-side from the authenticated user,
     * never from submitted form data.
     */
    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
