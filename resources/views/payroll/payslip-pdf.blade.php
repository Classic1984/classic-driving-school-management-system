<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $item->payee_name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; width: 160px; }
        .amount-row td { font-size: 16px; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Payslip — {{ $payrollRun->month->format('F Y') }} — Generated {{ now()->format('l, M j, Y g:i A') }}</h2>

    <table>
        <tr><th>Name</th><td>{{ $item->payee_name }}</td></tr>
        <tr><th>Pay Period</th><td>{{ $payrollRun->month->format('F Y') }}</td></tr>
        <tr><th>Processed</th><td>{{ $payrollRun->processed_at?->format('M j, Y') }}</td></tr>
        <tr class="amount-row"><th>Amount Paid</th><td class="text-right">₦{{ number_format($item->amount, 2) }}</td></tr>
    </table>
</body>
</html>
