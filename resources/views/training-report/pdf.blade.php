<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Training Report - {{ $label }}</title>
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
    <h2>Training Report — {{ $label }} — Generated {{ now()->format('Y-m-d H:i') }}</h2>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Training Date</th>
                <th>Session</th>
                <th>Instructor</th>
                <th>Vehicle</th>
                <th>Training Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->student->student_id_number }}</td>
                    <td>{{ $attendance->student->name }}</td>
                    <td>{{ $attendance->date->format('Y-m-d') }}</td>
                    <td>{{ $attendance->session ? ucfirst($attendance->session) : '—' }}</td>
                    <td>{{ $attendance->instructor?->name ?? '—' }}</td>
                    <td>{{ $attendance->vehicle ?? '—' }}</td>
                    <td>{{ $enrollmentStatuses["{$attendance->student_id}:{$attendance->course_id}"] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No students trained during this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
