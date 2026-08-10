<?php

namespace App\Models;

use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'instructor_id',
        'issue_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date:Y-m-d',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * certificate_number is deliberately not fillable: it's a permanent,
     * system-assigned identifier derived from the row's own auto-increment
     * id (like Student::student_id_number) so it's unique without a
     * collision-check loop, in the form CDS-CERT-{issue year}-{00001}. A
     * temporary placeholder satisfies the column's NOT NULL + unique
     * constraint for the moment before the row has an id to derive from.
     */
    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate) {
            $certificate->certificate_number = 'PENDING-'.Str::uuid();
        });

        static::created(function (Certificate $certificate) {
            $certificate->forceFill([
                'certificate_number' => sprintf(
                    'CDS-CERT-%s-%s',
                    $certificate->issue_date->format('Y'),
                    str_pad((string) $certificate->id, 5, '0', STR_PAD_LEFT)
                ),
            ])->save();
        });
    }
}
