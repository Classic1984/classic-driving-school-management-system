<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $student->name }} - Training Scan Code</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; margin: 0; }
        .card { border: 2px solid #1e3a5f; border-radius: 10px; padding: 30px 40px; text-align: center; }
        .brand { font-size: 18px; font-weight: bold; color: #1e3a5f; margin: 0; }
        .brand-sub { font-size: 10px; letter-spacing: 2px; color: #d97706; margin: 4px 0 0; text-transform: uppercase; }
        .student-name { font-size: 20px; font-weight: bold; color: #111827; margin: 24px 0 0; }
        .student-id { font-size: 12px; color: #6b7280; margin: 2px 0 0; }
        .instructions { font-size: 11px; color: #374151; margin: 24px 30px 0; }
        .note { font-size: 9px; color: #9ca3af; margin: 8px 30px 0; }
    </style>
</head>
<body>
    <div class="card">
        <p class="brand">CLASSIC DRIVING SCHOOL &amp; SON NIGERIA LIMITED</p>
        <p class="brand-sub">Training Scan Code</p>

        <p class="student-name">{{ $student->name }}</p>
        <p class="student-id">{{ $student->student_id_number }}</p>

        <p style="margin-top: 24px;">
            <img src="{{ app(\App\Services\QrCodeGenerator::class)->dataUri($student->trainingCheckInUrl(), 180) }}" width="180" height="180">
        </p>

        <p class="instructions">Scan this code at the front desk every training day to log your attendance automatically.</p>
        <p class="note">Keep this card - it is unique to you and works for every training day until your course ends.</p>
    </div>
</body>
</html>
