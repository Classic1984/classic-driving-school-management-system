<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PayrollSalary extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'payee_type',
        'payee_id',
        'monthly_amount',
        'active',
        'set_by',
    ];

    protected function casts(): array
    {
        return [
            'monthly_amount' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function payee(): MorphTo
    {
        return $this->morphTo();
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }
}
