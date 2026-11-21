<?php

namespace App\Models;

use Database\Factories\DeskStationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One camera-monitored desk/station (front desk, secretary's office,
 * etc.). The on-site agent watching that camera pings this record's
 * token every so often to report whether someone is currently there -
 * CDSMS never talks to the camera or DVR directly.
 */
class DeskStation extends Model
{
    /** @use HasFactory<DeskStationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_changed_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * How long an agent can go without pinging before the station is
     * treated as offline rather than trusting its last-reported status.
     */
    public const OFFLINE_AFTER_MINUTES = 2;

    public const STATUSES = [
        'occupied' => 'Occupied',
        'empty' => 'Empty',
        'unknown' => 'Unknown',
    ];

    protected static function booted(): void
    {
        static::creating(function (DeskStation $station) {
            $station->api_token ??= Str::random(48);
        });
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gt(now()->subMinutes(self::OFFLINE_AFTER_MINUTES));
    }

    /**
     * What to actually show for this station: its last-reported status
     * while the agent is still checking in, or "offline" once it's gone
     * quiet for too long - a stale "occupied" is worse than useless if
     * it's shown as current.
     */
    public function displayStatus(): string
    {
        return $this->isOnline() ? $this->status : 'offline';
    }

    /**
     * Record a status ping from the on-site agent. Not a mass-assignable
     * field - deliberately bypasses $fillable (via forceFill) the same
     * way Payment::receipt_number does, since this is only ever written
     * here, never from a web form.
     */
    public function recordPing(string $status): void
    {
        $this->forceFill([
            'status' => $status,
            'status_changed_at' => $this->status === $status ? $this->status_changed_at : now(),
            'last_seen_at' => now(),
        ])->save();
    }
}
