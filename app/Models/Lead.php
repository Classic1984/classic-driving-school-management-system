<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    /**
     * The statuses a lead can move through, from first inquiry to outcome.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'converted' => 'Converted',
        'lost' => 'Lost',
    ];

    /**
     * The fixed set of options offered for "how they heard about us" - kept
     * as a plain string column rather than a lookup table, so this list is
     * just what the dropdown offers, not an enforced constraint.
     *
     * @var list<string>
     */
    public const SOURCES = [
        'Walk-in',
        'Referral',
        'Phone Call',
        'Social Media',
        'Website',
        'Advertisement',
        'Other',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'course_interested',
        'source',
        'notes',
        'status',
        'last_reminded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_reminded_at' => 'datetime',
        ];
    }

    /**
     * Deposit payments received for this lead through the online booking
     * flow. A lead created that way always has exactly one - this stays a
     * hasMany, not a hasOne, so a future re-payment (e.g. after a failed
     * charge was retried) never has to fight the schema.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LeadPayment::class);
    }
}
