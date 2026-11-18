<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PayrollRunItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'payroll_run_id',
        'payee_type',
        'payee_id',
        'payee_name',
        'amount',
        'expense_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function payee(): MorphTo
    {
        return $this->morphTo();
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
