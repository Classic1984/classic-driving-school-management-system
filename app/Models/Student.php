<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
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
        'address',
        'date_of_birth',
        'license_number',
        'course_type',
        'enrollment_date',
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
            'date_of_birth' => 'date',
            'enrollment_date' => 'date',
        ];
    }

    /**
     * The courses this student is enrolled in.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }
}
