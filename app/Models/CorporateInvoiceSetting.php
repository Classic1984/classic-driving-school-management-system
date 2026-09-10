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
        'updated_by',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The one settings row, created on first access if it doesn't exist yet
     * rather than requiring a seeder - there's nothing meaningful to
     * pre-fill, the Director enters real bank/company details themselves.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
