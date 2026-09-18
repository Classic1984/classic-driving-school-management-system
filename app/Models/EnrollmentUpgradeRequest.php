<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A staff-submitted request to upgrade an enrollment (either the same-track
 * "longer programme" upgrade or a "tier" upgrade to Weekend/Executive/VIP -
 * see upgrade_type), pending Director approval. Submitting one never
 * changes the enrollment or collects any payment itself - a Director
 * approving this request is what actually calls EnrollmentService::upgrade()
 * (see EnrollmentUpgradeRequestController::approve()). Rejecting one leaves
 * the enrollment exactly as it was.
 */
class EnrollmentUpgradeRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'enrollment_id',
        'from_course_id',
        'to_course_id',
        'requested_by',
        'upgrade_type',
        'previous_fee',
        'new_fee',
        'upgrade_cost',
        'amount_paid',
        'payment_method',
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
            'previous_fee' => 'decimal:2',
            'new_fee' => 'decimal:2',
            'upgrade_cost' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function fromCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'from_course_id');
    }

    public function toCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'to_course_id');
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
