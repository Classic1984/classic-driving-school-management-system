<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, Notifiable;

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
        'mother_maiden_name',
        'sex',
        'state_of_origin',
        'local_government_area',
        'occupation',
        'next_of_kin_name',
        'next_of_kin_address',
        'next_of_kin_phone',
        'next_of_kin_email',
        'license_number',
        'course_type',
        'vehicle_class',
        'has_driving_experience',
        'wears_glasses',
        'referral_source',
        'referral_source_other',
        'photo_path',
        'id_document_path',
        'license_document_path',
        'enrollment_date',
        'status',
        'last_absence_reminder_sent_at',
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
            'has_driving_experience' => 'boolean',
            'wears_glasses' => 'boolean',
            'last_absence_reminder_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // student_id_number is deliberately not fillable: it's a permanent,
        // system-assigned identifier (not something a form should be able to
        // set or change), derived from the auto-increment id so it's unique
        // without a collision-check loop. Certificates embed it in their own
        // numbers so every document for a student is traceable back to the
        // same id.
        static::created(function (Student $student) {
            $student->forceFill([
                'student_id_number' => 'CDS-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT),
            ])->save();
        });
    }

    /**
     * The courses this student is enrolled in.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->using(Enrollment::class)
            ->withPivot(['id', 'enrolled_at', 'due_date', 'status', 'locked_reason', 'fee', 'original_fee', 'discount_percentage', 'discount_amount', 'discount_reason', 'discount_reason_note', 'discount_approved_by', 'reactivated_at', 'reactivation_fee', 'reactivated_by', 'online_certificate_fee', 'student_certificate_fee'])
            ->withTimestamps();
    }

    /**
     * The attendance records for this student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * The payment records for this student.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The certificates issued to this student.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * This student's charges for flat catalog services (e.g. Driver's
     * License Processing, Learner's Permit), independent of any course
     * enrollment.
     */
    public function studentServices(): HasMany
    {
        return $this->hasMany(StudentService::class);
    }

    /**
     * Recompute this student's overall status from their enrollments:
     * automatically completed once every course they're enrolled in has
     * completed, active otherwise. Runs whenever an enrollment completes or
     * a new one is added, so staff never have to set this by hand.
     * "Withdrawn" is a manual, administrative status and is never touched
     * by this automation.
     */
    public function refreshStatus(): void
    {
        if ($this->status === 'withdrawn') {
            return;
        }

        $enrollments = $this->courses()->get()->pluck('pivot');

        $newStatus = $enrollments->isNotEmpty() && $enrollments->every(fn (Enrollment $enrollment) => $enrollment->status === 'completed')
            ? 'completed'
            : 'active';

        if ($newStatus !== $this->status) {
            $this->forceFill(['status' => $newStatus])->save();
        }
    }
}
