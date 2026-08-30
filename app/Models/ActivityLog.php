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
     * A best-effort icon and color for this entry's timeline marker, guessed
     * from its free-text description - there's no structured "type" column,
     * every action just writes a human-readable sentence. Matched by
     * keyword, most specific first, with a generic fallback for anything
     * that doesn't match a known pattern.
     *
     * Returns a color *key*, not Tailwind classes - the caller (a blade
     * view) is responsible for mapping that key to literal class strings.
     * Tailwind's content scan only covers resources/views/**\/*.blade.php,
     * so a literal class string sitting in this file would never be picked
     * up by the build and would silently render with no styling at all.
     *
     * @return array{icon: string, color: string}
     */
    public function iconMeta(): array
    {
        $description = $this->description;

        return match (true) {
            str_contains($description, 'backup') => [
                'icon' => 'M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z',
                'color' => 'sky',
            ],
            str_contains($description, 'Re-sent') => [
                'icon' => 'M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5',
                'color' => 'purple',
            ],
            str_contains($description, 'Revoked') => [
                'icon' => 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636',
                'color' => 'red',
            ],
            str_contains($description, 'Granted') || str_contains($description, 'Enabled') => [
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'color' => 'green',
            ],
            str_contains($description, 'started processing') || str_contains($description, 'processing status') => [
                'icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
                'color' => 'blue',
            ],
            str_contains($description, 'Recorded a payment') => [
                'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z',
                'color' => 'green',
            ],
            str_contains($description, 'Charged') || str_contains($description, 'expense') => [
                'icon' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-5.25m-19.5 0h19.5',
                'color' => 'amber',
            ],
            str_contains($description, 'certificate') => [
                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                'color' => 'indigo',
            ],
            str_contains($description, 'correction') => [
                'icon' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125',
                'color' => 'orange',
            ],
            str_contains($description, 'Registered') || str_contains($description, 'Enrolled') => [
                'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z',
                'color' => 'teal',
            ],
            default => [
                'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
                'color' => 'gray',
            ],
        };
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
