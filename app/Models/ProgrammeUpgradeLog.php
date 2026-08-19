<?php

namespace App\Models;

use Database\Factories\ProgrammeUpgradeLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A permanent record of a student upgrading their training programme
 * mid-course (e.g. 2 Weeks -> 4 Weeks) within the five-day upgrade
 * window - who approved it, what it cost, and how far into training the
 * student was at the time. The enrollment row itself is updated in place
 * (its course_id changes), so this is the only place the "from" course
 * and the day-5-window context survive afterward.
 */
class ProgrammeUpgradeLog extends Model
{
    /** @use HasFactory<ProgrammeUpgradeLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'enrollment_id',
        'from_course_id',
        'to_course_id',
        'upgraded_by',
        'attended_days_at_upgrade',
        'previous_fee',
        'new_fee',
        'amount_charged',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_fee' => 'decimal:2',
            'new_fee' => 'decimal:2',
            'amount_charged' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'from_course_id');
    }

    public function toCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'to_course_id');
    }

    public function upgradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upgraded_by');
    }
}
