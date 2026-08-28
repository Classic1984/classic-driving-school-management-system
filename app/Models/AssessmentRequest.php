<?php

namespace App\Models;

use Database\Factories\AssessmentRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An instructor-submitted final practical assessment result, pending
 * Director confirmation. Submitting one never records an actual Assessment
 * or issues a certificate by itself - a Director approving this request is
 * what creates/updates the real Assessment (via
 * AssessmentRequestController::approve()), the same gate
 * Enrollment::maybeIssueCertificate() checks. Rejecting one discards the
 * recommendation without touching any existing Assessment.
 */
class AssessmentRequest extends Model
{
    /** @use HasFactory<AssessmentRequestFactory> */
    use HasFactory;

    public const RESULTS = ['pass', 'fail'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_id',
        'requested_by',
        'result',
        'score',
        'remarks',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
