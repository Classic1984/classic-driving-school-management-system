<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Training Report - {{ $label }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
        h3 { font-size: 13px; margin: 16px 0 4px; }
        h3 span { font-weight: normal; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Training Report — {{ $label }} — Generated {{ now()->format('Y-m-d H:i') }}</h2>

    @forelse ($attendancesByDate as $date => $dayAttendances)
        <h3>{{ \Illuminate\Support\Carbon::parse($date)->format('l, j F Y') }} <span>&middot; {{ trans_choice('{0} :count students|{1} :count student|[2,*] :count students', $dayAttendances->count(), ['count' => $dayAttendances->count()]) }}</span></h3>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Instructor</th>
                    <th>Training Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dayAttendances as $attendance)
                    <tr>
                        <td>{{ $attendance->student->student_id_number }}</td>
                        <td>{{ $attendance->student->name }}</td>
                        <td>{{ $attendance->type ? ucfirst($attendance->type) : '—' }}</td>
                        <td>{{ $attendance->duration ?? '—' }}</td>
                        <td>{{ $attendance->instructor?->name ?? '—' }}</td>
                        <td>{{ $enrollmentStatuses["{$attendance->student_id}:{$attendance->course_id}"] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>No students trained during this period.</p>
    @endforelse
</body>
</html>
