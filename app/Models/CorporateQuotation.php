<?php

namespace App\Models;

use Database\Factories\CorporateQuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateQuotation extends Model
{
    /** @use HasFactory<CorporateQuotationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'corporate_company_id',
        'programme_name',
        'duration_label',
        'participant_count',
        'course_coverage',
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
     * course_coverage is stored as one topic per line - same convention as
     * CorporateInvoice::courseCoverageList().
     *
     * @return array<int, string>
     */
    public function courseCoverageList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->course_coverage))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * "Expired" is a computed display state (like CorporateInvoice's
     * "Overdue"), not a stored status - draft/sent/approved/rejected/
     * converted are the only values the `status` column itself ever holds.
     */
    public function isExpired(): bool
    {
        return ! in_array($this->status, ['approved', 'rejected', 'converted'], true)
            && $this->valid_until !== null
            && $this->valid_until->isPast();
    }

    public function displayStatus(): string
    {
        return $this->isExpired() ? 'expired' : $this->status;
    }

    /**
     * quotation_number is deliberately not fillable: it's a permanent,
     * system-assigned identifier in the form {prefix}-{issue year}-
     * {00001}, using the same two-phase creating-then-created approach as
     * Certificate::certificate_number, but the sequence itself resets
     * every year (see CorporateInvoiceSetting::nextSequence()) rather than
     * counting up forever against the row's own auto-increment id.
     */
    protected static function booted(): void
    {
        static::created(function (CorporateQuotation $quotation) {
            $prefix = CorporateInvoiceSetting::current()->quotation_prefix ?: 'QUO';
            $year = (int) $quotation->issue_date->format('Y');
            $sequence = CorporateInvoiceSetting::nextSequence('quotation', $year);

            $quotation->forceFill([
                'quotation_number' => sprintf('%s-%d-%05d', $prefix, $year, $sequence),
            ])->save();
        });
    }
}
