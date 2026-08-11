<?php

namespace App\Models;

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
}
