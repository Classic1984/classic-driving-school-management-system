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

        return $kobo > 0
            ? self::spellNumber($naira).' Naira, '.self::spellNumber($kobo).' Kobo Only.'
            : self::spellNumber($naira).' Naira Only.';
    }

    /**
     * Spells out a non-negative integer in English words (e.g. 75000 ->
     * "Seventy-Five Thousand"). Written in plain PHP rather than using
     * PHP's intl NumberFormatter::SPELLOUT, because ext-intl isn't declared
     * anywhere in composer.json and so isn't guaranteed to be installed on
     * every deployment target - it happens to be present in local/CI
     * environments but was missing in production, which crashed this page
     * with a 500 the moment an invoice with a real total was opened.
     */
    private static function spellNumber(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $scales = ['', ' Thousand', ' Million', ' Billion', ' Trillion'];

        $chunkToWords = function (int $chunk) use ($ones, $tens): string {
            $words = [];
            if ($chunk >= 100) {
                $words[] = $ones[intdiv($chunk, 100)].' Hundred';
                $chunk %= 100;
            }
            if ($chunk >= 20) {
                $tensWord = $tens[intdiv($chunk, 10)];
                $remainder = $chunk % 10;
                $words[] = $remainder > 0 ? "{$tensWord}-{$ones[$remainder]}" : $tensWord;
            } elseif ($chunk > 0) {
                $words[] = $ones[$chunk];
            }

            return implode(' ', $words);
        };

        $chunks = [];
        while ($number > 0) {
            $chunks[] = $number % 1000;
            $number = intdiv($number, 1000);
        }

        $parts = [];
        foreach (array_reverse($chunks, true) as $index => $chunk) {
            if ($chunk === 0) {
                continue;
            }
            $parts[] = $chunkToWords($chunk).$scales[$index];
        }

        return implode(' ', $parts);
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
