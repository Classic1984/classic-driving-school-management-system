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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'service_id',
        'price',
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
}
