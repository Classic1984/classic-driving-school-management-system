<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateQuotation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'corporate_company_id',
        'status',
        'issue_date',
        'valid_until',
        'notes',
        'created_by',
        'sent_by',
        'sent_at',
        'converted_invoice_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date:Y-m-d',
            'valid_until' => 'date:Y-m-d',
            'sent_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CorporateCompany::class, 'corporate_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CorporateQuotationItem::class, 'corporate_quotation_id')->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(CorporateInvoice::class, 'converted_invoice_id');
    }

    public function total(): float
    {
        return $this->items->sum(fn (CorporateQuotationItem $item) => $item->amount());
    }

    /**
     * quotation_number is deliberately not fillable: it's a permanent,
     * system-assigned identifier derived from the row's own auto-increment
     * id, in the form {prefix}-{issue year}-{00001} - same two-phase
     * approach as Certificate::certificate_number.
     */
    protected static function booted(): void
    {
        static::created(function (CorporateQuotation $quotation) {
            $prefix = CorporateInvoiceSetting::current()->quotation_prefix ?: 'QUO';

            $quotation->forceFill([
                'quotation_number' => sprintf(
                    '%s-%s-%s',
                    $prefix,
                    $quotation->issue_date->format('Y'),
                    str_pad((string) $quotation->id, 5, '0', STR_PAD_LEFT)
                ),
            ])->save();
        });
    }
}
