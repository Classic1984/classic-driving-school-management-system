<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporatePayment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'corporate_invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'transaction_reference',
        'notes',
        'recorded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date:Y-m-d',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CorporateInvoice::class, 'corporate_invoice_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * receipt_number is deliberately not fillable: it's a permanent,
     * system-assigned identifier derived from the row's own auto-increment
     * id, in the form {prefix}-{payment year}-{00001} - same two-phase
     * approach as Payment::receipt_number.
     */
    protected static function booted(): void
    {
        static::created(function (CorporatePayment $payment) {
            $prefix = CorporateInvoiceSetting::current()->receipt_prefix ?: 'REC';

            $payment->forceFill([
                'receipt_number' => sprintf(
                    '%s-%s-%s',
                    $prefix,
                    $payment->payment_date->format('Y'),
                    str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT)
                ),
            ])->save();
        });
    }
}
