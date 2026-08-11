<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'reference_number',
        'notes',
        'recorded_by',
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
            'payment_date' => 'date:Y-m-d',
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

    /**
     * The staff member who recorded this payment. Null for historical
     * payments recorded before this was tracked.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * The individual charges (enrollment or flat service) this payment's
     * amount has been allocated across.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * Every correction ever made to how this payment's amount is split
     * across its charges, most recent first.
     */
    public function corrections(): HasMany
    {
        return $this->hasMany(PaymentCorrectionLog::class)->latest();
    }

    /**
     * A human-readable summary of what this payment paid for, e.g.
     * "Training — Beginner Course, Learner's Permit" - built from its
     * allocations so it's accurate whether this is a legacy single-course
     * payment or a multi-service one.
     */
    public function description(): string
    {
        $labels = $this->allocations->map(fn (PaymentAllocation $allocation) => $allocation->label());

        return $labels->isNotEmpty() ? $labels->implode(', ') : '—';
    }

    /**
     * receipt_number is deliberately not fillable: it's a permanent,
     * system-assigned identifier derived from the row's own auto-increment
     * id (like Certificate::certificate_number), in the form
     * CDS-RC-{payment year}-{00001}. A temporary placeholder satisfies the
     * column's NOT NULL + unique constraint for the moment before the row
     * has an id to derive from.
     */
    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->receipt_number = 'PENDING-'.Str::uuid();
        });

        static::created(function (Payment $payment) {
            $payment->forceFill([
                'receipt_number' => sprintf(
                    'CDS-RC-%s-%s',
                    $payment->payment_date->format('Y'),
                    str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT)
                ),
            ])->save();
        });

        // Keeps the training PaymentAllocation ledger in sync with the
        // legacy single-course payment flow: whenever a payment is saved
        // (created, updated, its amount corrected, its course changed),
        // its matching enrollment - if any - gets a training allocation
        // reflecting the payment's current amount. This means
        // Enrollment::amountPaid()/balance() can read allocations only,
        // without every payment-creating code path (factories, seeders,
        // the controller) needing to know allocations exist. The multi-
        // service payment entry screen creates allocations directly
        // itself; this keeps the existing single-course flow behaviorally
        // unchanged alongside it.
        static::saved(function (Payment $payment) {
            $enrollment = Enrollment::where('student_id', $payment->student_id)
                ->where('course_id', $payment->course_id)
                ->first();

            if ($enrollment === null) {
                PaymentAllocation::where('payment_id', $payment->id)
                    ->where('allocation_type', 'training')
                    ->delete();

                return;
            }

            PaymentAllocation::updateOrCreate(
                ['payment_id' => $payment->id, 'allocation_type' => 'training'],
                ['enrollment_id' => $enrollment->id, 'amount' => $payment->amount]
            );
        });
    }
}
