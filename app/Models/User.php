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
     * Whether this user has elevated (manager-level) access: managing
     * courses/instructors and deleting records across every resource.
     * Only Director has this. Admin and Secretary share the same
     * restricted access: viewing everything and creating/editing
     * students, attendance, payments, and tickets, with no deletion
     * rights and no course or instructor management.
     */
    public function isAdmin(): bool
    {
        return $this->isDirector();
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
