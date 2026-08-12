<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TheoryClassCancellationFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Marks one specific date's theory class as cancelled, so the weekly
 * reminder can text a cancellation notice instead of the usual "be
 * punctual" reminder - without touching the recurring schedule itself.
 */
class TheoryClassCancellation extends Model
{
    /** @use HasFactory<TheoryClassCancellationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'class_date',
        'reason',
        'cancelled_by',
    ];

    /**
     * Always stored as a plain "Y-m-d" string, and always read back as a
     * Carbon instance - the bare "date" cast persists a full
     * "Y-m-d H:i:s" value instead, which would break both the
     * unique-per-date validation and exact-date lookups.
     */
    protected function classDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Carbon::parse($value),
            set: fn ($value) => Carbon::parse($value)->toDateString(),
        );
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
