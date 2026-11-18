<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankStatementLine extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'bank_reconciliation_id',
        'line_date',
        'description',
        'amount',
        'status',
        'matched_type',
        'matched_id',
    ];

    protected function casts(): array
    {
        return [
            'line_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function bankReconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    /**
     * The Payment or Expense this bank line has been matched to, if any.
     */
    public function matched(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'matched_type', 'matched_id');
    }

    public function isMatched(): bool
    {
        return $this->status === 'matched';
    }

    public function isIgnored(): bool
    {
        return $this->status === 'ignored';
    }

    public function isInflow(): bool
    {
        return (float) $this->amount >= 0;
    }
}
