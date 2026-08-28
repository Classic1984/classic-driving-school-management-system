<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TheoryClassFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TheoryClass extends Model
{
    /** @use HasFactory<TheoryClassFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'class_date',
        'start_time',
        'topic',
        'instructor_id',
        'notes',
        'materials_path',
        'materials_original_name',
        'created_by',
    ];

    /**
     * Always stored as a plain "Y-m-d" string, and always read back as a
     * Carbon instance - the bare "date" cast persists a full
     * "Y-m-d H:i:s" value instead, which would break exact-date lookups.
     */
    protected function classDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Carbon::parse($value),
            set: fn ($value) => Carbon::parse($value)->toDateString(),
        );
    }

    /**
     * Always read back as a plain "H:i" string, even though the database
     * normalizes a TIME column to "H:i:s" - without this, redisplaying an
     * already-saved value in the edit form's time input and resubmitting
     * it unchanged fails that field's own "H:i" validation rule.
     */
    protected function startTime(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : substr($value, 0, 5),
        );
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TheoryClassAttendance::class);
    }

    /**
     * Every student with at least one active enrollment - the roster this
     * class is expected to draw from, regardless of which course each
     * student is training in.
     */
    public function expectedStudents()
    {
        return Student::whereHas('courses', fn ($query) => $query->where('course_student.status', 'active'))->get();
    }

    public function presentCount(): int
    {
        return $this->attendances->whereIn('status', ['present', 'late'])->count();
    }

    public function absentCount(): int
    {
        return $this->attendances->where('status', 'absent')->count();
    }

    public function expectedCount(): int
    {
        return $this->expectedStudents()->count();
    }

    /**
     * Percentage of the expected roster marked present (or late), rounded
     * to the nearest whole number. Zero when nobody was expected, rather
     * than dividing by zero.
     */
    public function attendancePercentage(): int
    {
        $expected = $this->expectedCount();

        if ($expected === 0) {
            return 0;
        }

        return (int) round(($this->presentCount() / $expected) * 100);
    }

    /**
     * Public URL for the uploaded lecture material, or null if none has
     * been uploaded for this class.
     */
    public function materialsUrl(): ?string
    {
        return $this->materials_path ? Storage::disk('public')->url($this->materials_path) : null;
    }
}
