<?php

namespace App\Models;

use Database\Factories\PaymentCorrectionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A structured, permanent record of a correction to how a payment's
 * amount was split across its charges: who made it, when, why, and the
 * exact before/after allocation. Payments are never silently edited -
 * every correction leaves this trail for Director oversight.
 */
class PaymentCorrectionLog extends Model
{
    /** @use HasFactory<PaymentCorrectionLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment_id',
        'corrected_by',
        'reason',
        'original_allocations',
        'new_allocations',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'original_allocations' => 'array',
            'new_allocations' => 'array',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
