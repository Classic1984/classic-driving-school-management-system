<?php

namespace App\Models;

use Database\Factories\CorporateQuotationItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateQuotationItem extends Model
{
    /** @use HasFactory<CorporateQuotationItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'corporate_quotation_id',
        'description',
        'quantity',
        'unit_price',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(CorporateQuotation::class, 'corporate_quotation_id');
    }

    public function amount(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }
}
