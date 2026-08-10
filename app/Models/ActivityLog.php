<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * A general-purpose "who did what and when" trail across the system's
 * everyday actions (registering a student, recording a payment, logging
 * attendance, etc.), independent from the narrower, structured
 * DiscountAuditLog/ReactivationAuditLog which track those two specific
 * events with their own dedicated fields.
 */
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an action taken by the currently authenticated user (or the
     * given user, for console/system-triggered actions with no request).
     */
    public static function record(string $description, ?User $user = null): self
    {
        return static::create([
            'user_id' => ($user ?? Auth::user())?->id,
            'description' => $description,
        ]);
    }
}
