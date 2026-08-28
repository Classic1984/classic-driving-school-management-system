<?php

namespace App\Models;

use Database\Factories\InstructorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Instructor extends Model
{
    /** @use HasFactory<InstructorFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'license_number',
        'specialization',
        'hire_date',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    /**
     * The courses this instructor is assigned to teach.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    /**
     * The login account for this instructor's app access, if a Director
     * or Secretary has granted it. user_id is deliberately not
     * mass-assignable - only InstructorAccessController sets it, never a
     * submitted form field.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether this instructor has app access granted at all, regardless
     * of whether they've completed their first-login PIN setup yet.
     */
    public function hasAppAccess(): bool
    {
        return $this->user_id !== null;
    }
}
