<?php

namespace App\Models;

use Database\Factories\TheoryClassAttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheoryClassAttendance extends Model
{
    /** @use HasFactory<TheoryClassAttendanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'theory_class_id',
        'student_id',
        'status',
        'score',
        'remarks',
        'marked_by',
    ];

    public function theoryClass(): BelongsTo
    {
        return $this->belongsTo(TheoryClass::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * The staff member who recorded this attendance. Deliberately not
     * mass-assignable: always set server-side from the authenticated user
     * (or left null for the automated end-of-day absence sweep), never
     * from submitted form data.
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
