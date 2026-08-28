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
     * The public verification page for this certificate - what the QR
     * code printed on it actually encodes. Scanning it looks the
     * certificate up live against the database, rather than just
     * redisplaying text that could be copied onto a forged certificate.
     */
    public function verificationUrl(): string
    {
        return route('certificates.verify', $this->certificate_number);
    }

    /**
     * A plain-text summary of this certificate's holder, program, and
     * instructor - kept for the certificate's own printed detail, not the
     * QR code (see verificationUrl() above).
     */
    public function qrCodeSummary(): string
    {
        return implode("\n", array_filter([
            'Classic Driving School & Son Nigeria Limited',
            "Certificate No.: {$this->certificate_number}",
            "Name: {$this->student->name}",
            "Student ID: {$this->student->student_id_number}",
            "Program: {$this->course->name}",
            $this->instructor ? "Instructor: {$this->instructor->name}" : null,
        ]));
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
