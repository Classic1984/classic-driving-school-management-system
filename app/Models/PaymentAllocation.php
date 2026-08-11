<?php

namespace App\Models;

use Database\Factories\PaymentAllocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of a payment: the portion of a payment applied to a single
 * charge - a course enrollment's training fee, one of its course-outline
 * certificate fees, or a flat catalog service. A single Payment can have
 * several allocations, letting one transaction fund multiple charges at
 * once while keeping each charge's own balance accurate.
 */
class PaymentAllocation extends Model
{
    /** @use HasFactory<PaymentAllocationFactory> */
    use HasFactory;

    /**
     * The kinds of charge a payment allocation can apply to.
     */
    public const TYPES = ['training', 'online_certificate', 'student_certificate', 'service'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment_id',
        'allocation_type',
        'enrollment_id',
        'student_service_id',
        'amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * The enrollment this allocation applies to, for the training,
     * online_certificate, and student_certificate allocation types.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    /**
     * The flat catalog service charge this allocation applies to, for the
     * service allocation type.
     */
    public function studentService(): BelongsTo
    {
        return $this->belongsTo(StudentService::class);
    }
}
