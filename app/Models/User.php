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
        ];
    }

    /**
     * Directors have all admin capabilities, plus exclusive access to the
     * Finance section (income/expenses).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'director'], true);
    }

    public function isDirector(): bool
    {
        return $this->role === 'director';
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
