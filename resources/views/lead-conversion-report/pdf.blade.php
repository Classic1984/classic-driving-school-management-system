<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lead Conversion Report - {{ $label }}</title>
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
    <h2>Lead Conversion Report — {{ $label }} — Generated {{ now()->format('Y-m-d H:i') }}</h2>

    <table class="summary">
        <thead>
            <tr>
                <th>Total Leads</th>
                <th>New</th>
                <th>Contacted</th>
                <th>Converted</th>
                <th>Lost</th>
                <th>Conversion Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $summary['total'] }}</td>
                <td>{{ $summary['new'] }}</td>
                <td>{{ $summary['contacted'] }}</td>
                <td>{{ $summary['converted'] }}</td>
                <td>{{ $summary['lost'] }}</td>
                <td>{{ $summary['rate'] }}%</td>
            </tr>
        </tbody>
    </table>

    <h3>By Source</h3>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th>Total</th>
                <th>New</th>
                <th>Contacted</th>
                <th>Converted</th>
                <th>Lost</th>
                <th>Conversion Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['source'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['new'] }}</td>
                    <td>{{ $row['contacted'] }}</td>
                    <td>{{ $row['converted'] }}</td>
                    <td>{{ $row['lost'] }}</td>
                    <td>{{ $row['rate'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No inquiries were logged during this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
