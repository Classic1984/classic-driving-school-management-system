<?php

namespace App\Models;

use Database\Factories\StudentCorrectionRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A staff-submitted request to change one of a student's Director-locked
 * fields (name, date of birth, phone, or training program). Submitting one
 * never changes the student record itself - a Director reviews it and
 * makes the actual change (via the normal edit form, or the course roster
 * for a program change), then marks the request resolved or rejected here.
 */
class StudentCorrectionRequest extends Model
{
    /** @use HasFactory<StudentCorrectionRequestFactory> */
    use HasFactory;

    /**
     * The fields a correction can be requested for.
     */
    public const FIELDS = ['name', 'date_of_birth', 'phone', 'program'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'requested_by',
        'field',
        'current_value',
        'requested_value',
        'reason',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * A human-readable label for the locked field this request concerns.
     */
    public function fieldLabel(): string
    {
        return match ($this->field) {
            'name' => 'Name',
            'date_of_birth' => 'Date of Birth',
            'phone' => 'Phone',
            'program' => 'Training Program',
            default => ucfirst($this->field),
        };
    }
}
