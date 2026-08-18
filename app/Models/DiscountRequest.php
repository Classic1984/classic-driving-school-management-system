<?php

namespace App\Models;

use Database\Factories\DiscountRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A staff-submitted request to apply a discount to an enrollment, pending
 * Director approval. Submitting one never discounts the enrollment's fee
 * itself - the enrollment is created at full price, and a Director
 * approving this request is what actually reduces it (and logs a
 * DiscountAuditLog entry, the same as a Director applying a discount
 * directly). Rejecting one leaves the enrollment at full price for good.
 */
class DiscountRequest extends Model
{
    /** @use HasFactory<DiscountRequestFactory> */
    use HasFactory;

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
        'original_fee',
        'discount_percentage',
        'discount_amount',
        'final_fee',
        'reason',
        'reason_note',
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
            'original_fee' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_fee' => 'decimal:2',
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
