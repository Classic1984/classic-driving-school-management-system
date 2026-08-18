<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\StudentServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A student's charge for a flat catalog Service - independent of any
 * course enrollment. The price is snapshotted at charge time, the same
 * way Enrollment::fee snapshots a course's fee.
 */
class StudentService extends Model
{
    /** @use HasFactory<StudentServiceFactory> */
    use HasFactory;

    /**
     * The stages a service's real-world processing can be in - entirely
     * independent of how much of it has been paid for.
     */
    public const PROCESSING_STATUSES = ['not_started', 'processing', 'completed'];

    /**
     * Mirrors the column's DB-level default so a freshly instantiated
     * model reflects it immediately, without waiting on a re-fetch.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'processing_status' => 'not_started',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'service_id',
        'price',
        'processing_status',
        'processing_started_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'processing_started_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * The total amount paid toward this charge so far, across every
     * payment allocation recorded against it.
     */
    public function amountPaid(): float
    {
        return (float) $this->allocations()
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->sum('amount');
    }

    /**
     * The remaining balance owed for this charge.
     */
    public function balance(): float
    {
        return max(0, (float) $this->price - $this->amountPaid());
    }

    /**
     * This charge's payment status: unpaid, part_payment, or paid.
     */
    public function status(): string
    {
        if ($this->amountPaid() <= 0) {
            return 'unpaid';
        }

        return $this->balance() > 0 ? 'part_payment' : 'paid';
    }

    /**
     * A human-readable label for this service's real-world processing
     * status, e.g. "Not Started", "Processing", "Completed".
     */
    public function processingStatusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->processing_status));
    }

    /**
     * The date this service's processing is expected to be ready by,
     * based on when it started plus its catalog service's typical
     * turnaround - or null if processing hasn't started yet, or the
     * service has no tracked turnaround (e.g. Learner's Permit).
     */
    public function expectedReadyAt(): ?Carbon
    {
        if ($this->processing_started_at === null || $this->service->processing_days === null) {
            return null;
        }

        return $this->processing_started_at->copy()->addDays($this->service->processing_days);
    }

    /**
     * How far through its expected turnaround this service's processing
     * is, from 0 to 100 - or null if there's nothing to measure progress
     * against. Once marked completed, progress is always full regardless
     * of how much of the expected turnaround has actually elapsed.
     */
    public function processingProgressPercent(): ?int
    {
        if ($this->processing_status === 'completed') {
            return 100;
        }

        if ($this->processing_started_at === null || $this->service->processing_days === null) {
            return null;
        }

        $elapsedDays = $this->processing_started_at->diffInDays(now());

        return (int) min(100, floor($elapsedDays / $this->service->processing_days * 100));
    }

    /**
     * Whether this service has run past its expected turnaround without
     * being marked completed.
     */
    public function isOverdueProcessing(): bool
    {
        $expectedReadyAt = $this->expectedReadyAt();

        return $expectedReadyAt !== null && $expectedReadyAt->isPast() && $this->processing_status !== 'completed';
    }

    /**
     * The first payment recorded toward this service starts its
     * processing clock automatically, if it has a tracked turnaround and
     * hasn't started yet - staff don't have to switch it to "Processing"
     * by hand. A service already processing or completed, or one with no
     * turnaround to track, is left alone.
     */
    public function maybeAutoStartProcessing(): void
    {
        $this->loadMissing(['service', 'student']);

        if ($this->service->processing_days === null || $this->processing_status !== 'not_started') {
            return;
        }

        $this->update(['processing_status' => 'processing', 'processing_started_at' => now()]);

        ActivityLog::record("Payment started processing for {$this->service->name} ({$this->student->name})");
    }
}
