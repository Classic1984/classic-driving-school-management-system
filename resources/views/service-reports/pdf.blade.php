<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $service->name }} Report - {{ $label }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>{{ $service->name }} Report — {{ $label }} — Generated {{ now()->format('Y-m-d H:i') }}</h2>
    <p><strong>Total completed:</strong> {{ $completed->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Charged Date</th>
                <th>Completed Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($completed as $studentService)
                <tr>
                    <td>{{ $studentService->student->student_id_number }}</td>
                    <td>{{ $studentService->student->name }}</td>
                    <td>{{ $studentService->created_at->format('Y-m-d') }}</td>
                    <td>{{ $studentService->updated_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No {{ $service->name }} charges were completed during this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
