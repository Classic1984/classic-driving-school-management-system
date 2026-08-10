<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate Report - {{ $label }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Certificate Report — {{ $label }} — Generated {{ now()->format('Y-m-d H:i') }}</h2>

    <table>
        <thead>
            <tr>
                <th>Certificate #</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Instructor</th>
                <th>Issue Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($certificates as $certificate)
                <tr>
                    <td>{{ $certificate->certificate_number }}</td>
                    <td>{{ $certificate->student->student_id_number }}</td>
                    <td>{{ $certificate->student->name }}</td>
                    <td>{{ $certificate->course->name }}</td>
                    <td>{{ $certificate->instructor?->name ?? '—' }}</td>
                    <td>{{ $certificate->issue_date->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No certificates issued during this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
