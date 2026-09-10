<?php

namespace App\Models;

use Database\Factories\CorporateInvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateInvoice extends Model
{
    /** @use HasFactory<CorporateInvoiceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'corporate_company_id',
        'corporate_quotation_id',
        'invoice_date',
        'due_date',
        'programme_name',
        'duration_label',
        'participant_count',
        'course_coverage',
        'status',
        'created_by',
        'sent_by',
        'sent_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date:Y-m-d',
            'due_date' => 'date:Y-m-d',
            'sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CorporateCompany::class, 'corporate_company_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(CorporateQuotation::class, 'corporate_quotation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CorporateInvoiceItem::class, 'corporate_invoice_id')->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CorporatePayment::class, 'corporate_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function total(): float
    {
        return $this->items->sum(fn (CorporateInvoiceItem $item) => $item->amount());
    }

    public function amountPaid(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function balance(): float
    {
        return max(0, $this->total() - $this->amountPaid());
    }

    /**
     * The total, spelled out for the printed document (e.g. "Seventy-Five
     * Thousand Naira Only."), the way a paper invoice traditionally repeats
     * the amount in words to guard against a figure being altered.
     */
    public function totalInWords(): string
    {
        $total = $this->total();
        $naira = (int) floor($total);
        $kobo = (int) round(($total - $naira) * 100);

        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        $spell = fn (int $n) => ucwords($formatter->format($n), " \t\r\n\f\v-");

        return $kobo > 0
            ? "{$spell($naira)} Naira, {$spell($kobo)} Kobo Only."
            : "{$spell($naira)} Naira Only.";
    }

    /**
     * course_coverage is stored as one topic per line - split into a clean
     * list for the printed document, dropping blank lines so stray
     * whitespace in the textarea doesn't render as empty rows.
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
     * "Overdue" is a computed display state, not a stored status - the
     * invoice's own `status` column only ever holds pending/sent/paid/
     * cancelled (see the migration), so this stays accurate without a
     * scheduled job.
     */
    public function isOverdue(): bool
    {
        return ! in_array($this->status, ['paid', 'cancelled'], true) && $this->due_date->isPast();
    }

    public function displayStatus(): string
    {
        return $this->isOverdue() ? 'overdue' : $this->status;
    }

    /**
     * invoice_number is deliberately not fillable: it's a permanent,
     * system-assigned identifier derived from the row's own auto-increment
     * id, in the form {prefix}-{invoice year}-{00001} - same two-phase
     * approach as Certificate::certificate_number.
     */
    protected static function booted(): void
    {
        static::created(function (CorporateInvoice $invoice) {
            $prefix = CorporateInvoiceSetting::current()->invoice_prefix ?: 'INV';

            $invoice->forceFill([
                'invoice_number' => sprintf(
                    '%s-%s-%s',
                    $prefix,
                    $invoice->invoice_date->format('Y'),
                    str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT)
                ),
            ])->save();
        });
    }
}
