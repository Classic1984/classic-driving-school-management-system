<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * The individual charges (enrollment or flat service) this payment's
     * amount has been allocated across.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    protected static function booted(): void
    {
        // Keeps the training PaymentAllocation ledger in sync with the
        // legacy single-course payment flow: whenever a payment is saved
        // (created, updated, its amount corrected, its course changed),
        // its matching enrollment - if any - gets a training allocation
        // reflecting the payment's current amount. This means
        // Enrollment::amountPaid()/balance() can read allocations only,
        // without every payment-creating code path (factories, seeders,
        // the controller) needing to know allocations exist. A multi-
        // service payment entry screen will create allocations directly
        // in a later phase; this keeps the existing single-course flow
        // behaviorally unchanged in the meantime.
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
