<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single-row settings table holding the company/bank details and
 * document-number prefixes printed on every corporate quotation, invoice,
 * and receipt.
 */
class CorporateInvoiceSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_name',
        'tagline',
        'slogan',
        'address',
        'bank_account_name',
        'bank_name',
        'bank_account_number',
        'phone',
        'email',
        'signature_path',
        'invoice_prefix',
        'quotation_prefix',
        'receipt_prefix',
        'payment_terms',
        'updated_by',
    ];

    /**
     * payment_terms is stored as one bullet per line - split into a clean
     * list for the printed document, same convention as
     * CorporateInvoice::courseCoverageList().
     *
     * @return array<int, string>
     */
    public function paymentTermsList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->payment_terms))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The one settings row, created on first access if it doesn't exist yet
     * rather than requiring a seeder - there's nothing meaningful to
     * pre-fill except the numbering prefixes, which the Director can still
     * change later. The prefix defaults are passed explicitly here (not
     * left to the migration's column default) because Eloquent doesn't
     * re-read column defaults back into the model after an insert - without
     * this, the freshly created instance's prefix attributes would be null
     * in PHP even though the database row itself correctly has "QUO" etc.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QUO',
            'receipt_prefix' => 'REC',
        ]);
    }
}
