<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate {{ $certificate->certificate_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #ffffff; margin: 0; }
        .card { background: #000000; border: 2px solid #d97706; border-radius: 10px; padding: 30px 40px; text-align: center; }
        .top-row { width: 100%; }
        .top-row td { font-size: 10px; vertical-align: top; }
        .cert-no-label { text-align: right; color: #fbbf24; text-transform: uppercase; letter-spacing: 1px; }
        .cert-no-value { text-align: right; color: #fcd34d; font-size: 13px; }
        .brand { font-size: 22px; font-weight: bold; letter-spacing: 4px; color: #ffffff; margin: 14px 0 0; }
        .brand-sub { font-size: 10px; letter-spacing: 3px; color: #fbbf24; margin: 2px 0 0; text-transform: uppercase; }
        .title { font-size: 26px; font-weight: bold; color: #fbbf24; margin: 18px 0 0; }
        .lead { font-size: 12px; color: #d1d5db; margin: 20px 0 0; }
        .student-name { font-size: 32px; font-weight: bold; color: #fbbf24; margin: 10px 0 0; }
        .body-text { font-size: 12px; color: #d1d5db; margin: 16px 40px 0; }
        .course-name { font-size: 16px; font-weight: bold; color: #fbbf24; text-transform: uppercase; margin: 4px 0 0; }
        .details-table { width: 100%; margin-top: 24px; background: #1f2937; border: 1px solid rgba(217, 119, 6, 0.4); border-radius: 8px; }
        .details-table td { padding: 12px 6px; text-align: center; font-size: 10px; width: 25%; }
        .details-label { color: #fbbf24; text-transform: uppercase; letter-spacing: 1px; font-size: 8px; display: block; margin-bottom: 3px; }
        .details-value { color: #ffffff; font-size: 11px; }
        .signatures-table { width: 100%; margin-top: 30px; }
        .signatures-table td { width: 33%; text-align: center; font-size: 10px; padding-top: 8px; border-top: 1px solid rgba(217, 119, 6, 0.5); vertical-align: top; }
        .signature-role { color: #fbbf24; text-transform: uppercase; font-size: 8px; letter-spacing: 1px; }
        .qr-cell { border-top: none !important; }
        .verify-note { margin-top: 16px; font-size: 8px; color: #9ca3af; }
        .footer { margin-top: 20px; font-size: 9px; color: #fbbf24; font-style: italic; }
    </style>
</head>
<body>
    <div class="card">
        <table class="top-row">
            <tr>
                <td style="text-align: left; width: 50%;"></td>
                <td style="width: 50%;">
                    <span class="cert-no-label">Certificate No.</span><br>
                    <span class="cert-no-value">{{ $certificate->certificate_number }}</span>
                </td>
            </tr>
        </table>

        <p class="brand">CLASSIC DRIVING SCHOOL</p>
        <p class="brand-sub">&amp; Son Nigeria Limited</p>

        <p class="title">Certificate of Training Completion</p>

        <p class="lead">This is to certify that</p>
        <p class="student-name">{{ $certificate->student->name }}</p>

        <p class="body-text">has successfully completed the approved training program and satisfied all the requirements in</p>
        <p class="course-name">{{ $certificate->course->name }}</p>
        <p class="body-text" style="margin-top: 2px;">and is hereby awarded this certificate.</p>

        <table class="details-table">
            <tr>
                <td>
                    <span class="details-label">Student ID</span>
                    <span class="details-value">{{ $certificate->student->student_id_number }}</span>
                </td>
                <td>
                    <span class="details-label">Date of Completion</span>
                    <span class="details-value">{{ $certificate->issue_date->format('M j, Y') }}</span>
                </td>
                <td>
                    <span class="details-label">Duration</span>
                    <span class="details-value">{{ $certificate->course->duration_weeks }} weeks ({{ $certificate->course->totalTrainingDays() }} hrs)</span>
                </td>
                <td>
                    <span class="details-label">Level</span>
                    <span class="details-value">{{ $certificate->course->level ? ucfirst($certificate->course->level) : '—' }}</span>
                </td>
            </tr>
        </table>

        <table class="signatures-table">
            <tr>
                <td>
                    {{ $certificate->instructor?->name }}&nbsp;<br>
                    <span class="signature-role">Chief Instructor</span>
                </td>
                <td class="qr-cell">
                    <img src="{{ app(\App\Services\QrCodeGenerator::class)->dataUri($certificate->verificationUrl(), 90) }}" width="90" height="90">
                </td>
                <td>
                    &nbsp;<br>
                    <span class="signature-role">Managing Director</span>
                </td>
            </tr>
        </table>

        <p class="verify-note">Scan the QR code or visit {{ $certificate->verificationUrl() }} to verify this certificate.</p>

        <p class="footer">"When you say Classic, you say it all."</p>
    </div>
</body>
</html>
