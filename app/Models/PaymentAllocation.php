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
    public const TYPES = ['training', 'online_certificate', 'student_certificate', 'service', 'reactivation_fee'];

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

    /**
     * A human-readable label for the charge this allocation applies to,
     * e.g. "Training — Beginner Course" or "Learner's Permit".
     */
    public function label(): string
    {
        return match ($this->allocation_type) {
            'training' => "Training — {$this->enrollment->course->name}",
            'online_certificate' => "Online Certificate — {$this->enrollment->course->name}",
            'student_certificate' => "Student Certificate — {$this->enrollment->course->name}",
            'service' => $this->studentService->service->name,
            'reactivation_fee' => "Reactivation Fee — {$this->enrollment->course->name}",
            default => ucfirst(str_replace('_', ' ', $this->allocation_type)),
        };
    }

    /**
     * The current remaining balance on the underlying charge this
     * allocation applies to (i.e. after this and every other allocation
     * against it), not the amount of this allocation itself.
     */
    public function chargeBalance(): float
    {
        return match ($this->allocation_type) {
            'training' => $this->enrollment->balance(),
            'online_certificate' => $this->enrollment->onlineCertificateBalance() ?? 0.0,
            'student_certificate' => $this->enrollment->studentCertificateBalance() ?? 0.0,
            'service' => $this->studentService->balance(),
            default => 0.0,
        };
    }

    /**
     * A key identifying the underlying charge this allocation applies to,
     * stable across every allocation ever made against the same charge -
     * used to de-duplicate a payment's allocations down to the distinct
     * charges it touched.
     */
    public function chargeKey(): string
    {
        return "{$this->allocation_type}:".($this->enrollment_id ?? $this->student_service_id);
    }
}
