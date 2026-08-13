<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
