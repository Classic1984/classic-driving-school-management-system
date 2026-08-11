<?php

namespace App\Models;

use Database\Factories\PaymentReversalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reversal of a payment: the school never deletes a payment record to
 * undo it (deleting would erase the audit trail), it reverses it instead.
 * The original payment stays on file - its status just moves to
 * "refunded", which automatically excludes it from every balance
 * calculation - and this row records who reversed it, when, and why.
 */
class PaymentReversal extends Model
{
    /** @use HasFactory<PaymentReversalFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment_id',
        'reversed_by',
        'amount',
        'reason',
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

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
