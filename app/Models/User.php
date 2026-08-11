<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Whether this user has finished setting up (confirmed) two-factor
     * authentication. Requires both a secret and a confirmation timestamp -
     * a confirmed_at with no secret would be a broken, unloginable state,
     * since the two-factor challenge would have nothing to verify against.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Whether this user has elevated (manager-level) access: deleting
     * records across every resource. Only Director has this.
     */
    public function isAdmin(): bool
    {
        return $this->isDirector();
    }

    public function isDirector(): bool
    {
        return $this->role === 'director';
    }

    public function isSecretary(): bool
    {
        return $this->role === 'secretary';
    }

    /**
     * Whether this user can create/edit (but not delete) courses and
     * instructors. Secretary and Director have this; Admin does not.
     */
    public function canManageCourses(): bool
    {
        return $this->isSecretary() || $this->isDirector();
    }

    /**
     * Admins and Directors — the users who should be notified about
     * enrollment/payment events that need staff attention.
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('role', ['admin', 'director']);
    }
}
