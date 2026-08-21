<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Referral Source Report - {{ $label }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        h3 { font-size: 13px; margin-top: 20px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
        .summary td { text-align: center; }
        .summary th { text-align: center; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Referral Source Report — {{ $label }} — Generated {{ now()->format('Y-m-d H:i') }}</h2>

    <table class="summary">
        <thead>
            <tr>
                <th>Total Students</th>
                <th>Revenue Collected</th>
                <th>Outstanding Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $summary['total'] }}</td>
                <td>₦{{ number_format($summary['revenue'], 2) }}</td>
                <td>₦{{ number_format($summary['outstanding'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>By Source</h3>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th>Total</th>
                <th>Active</th>
                <th>Completed</th>
                <th>Withdrawn</th>
                <th>Completion Rate</th>
                <th>Revenue Collected</th>
                <th>Outstanding</th>
                <th>Avg. Revenue / Student</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['source'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['active'] }}</td>
                    <td>{{ $row['completed'] }}</td>
                    <td>{{ $row['withdrawn'] }}</td>
                    <td>{{ $row['completion_rate'] }}%</td>
                    <td>₦{{ number_format($row['revenue'], 2) }}</td>
                    <td>₦{{ number_format($row['outstanding'], 2) }}</td>
                    <td>₦{{ number_format($row['avg_revenue'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No students registered during this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
