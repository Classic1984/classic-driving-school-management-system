<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Reports - {{ $date }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        h3 { font-size: 14px; margin-top: 24px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
        tfoot td { font-weight: bold; border-top: 2px solid #9ca3af; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Financial Reports — Generated {{ now()->format('Y-m-d H:i') }}</h2>

    <h3>Daily Payment Report — {{ $date }}</h3>
    <table>
        <tbody>
            <tr><td>Number of Payments</td><td class="text-right">{{ $daily['count'] }}</td></tr>
            <tr><td>Cash</td><td class="text-right">₦{{ number_format($daily['cash'], 2) }}</td></tr>
            <tr><td>Transfers</td><td class="text-right">₦{{ number_format($daily['bank_transfer'], 2) }}</td></tr>
            <tr><td>POS</td><td class="text-right">₦{{ number_format($daily['card'], 2) }}</td></tr>
            <tr><td>Online</td><td class="text-right">₦{{ number_format($daily['mobile_money'], 2) }}</td></tr>
            <tr><td>Services Paid For</td><td>{{ $daily['services']->implode(', ') ?: '—' }}</td></tr>
        </tbody>
    </table>

    <h3>Service Revenue Report (All Time)</h3>
    <table>
        <thead>
            <tr><th>Service</th><th class="text-right">Revenue</th></tr>
        </thead>
        <tbody>
            @foreach ($revenueByService as $service => $amount)
                <tr><td>{{ $service }}</td><td class="text-right">₦{{ number_format($amount, 2) }}</td></tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td>Total</td><td class="text-right">₦{{ number_format($revenueByService->sum(), 2) }}</td></tr>
        </tfoot>
    </table>

    <h3>Outstanding Report</h3>
    <table>
        <thead>
            <tr><th>Student</th><th>Service</th><th class="text-right">Total</th><th class="text-right">Paid</th><th class="text-right">Balance</th></tr>
        </thead>
        <tbody>
            @foreach ($outstanding as $row)
                <tr>
                    <td>{{ $row['student'] }}</td>
                    <td>{{ $row['label'] }}</td>
                    <td class="text-right">₦{{ number_format($row['price'], 2) }}</td>
                    <td class="text-right">₦{{ number_format($row['paid'], 2) }}</td>
                    <td class="text-right">₦{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="4">Total Outstanding</td><td class="text-right">₦{{ number_format($outstanding->sum('balance'), 2) }}</td></tr>
        </tfoot>
    </table>
</body>
</html>
