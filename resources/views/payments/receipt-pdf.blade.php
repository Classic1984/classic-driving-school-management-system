<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        h3 { font-size: 14px; margin-top: 20px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
        tfoot td { font-weight: bold; border-top: 2px solid #9ca3af; }
        .text-right { text-align: right; }
        .student-info { margin-top: 16px; }
        .student-info td { border: none; padding: 2px 8px 2px 0; }
        .student-info th { border: none; background: none; text-transform: none; font-size: 12px; font-weight: bold; color: #111827; width: 140px; }
        .no-refund { margin-top: 24px; text-align: center; font-size: 10px; font-weight: bold; color: #dc2626; }
        .signatures { width: 100%; margin-top: 32px; }
        .signatures td { border: none; border-top: 1px solid #9ca3af; padding-top: 4px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Official Payment Receipt — {{ $payment->receipt_number }} — {{ $payment->payment_date->format('l, M j, Y') }}</h2>

    <table class="student-info">
        <tr><th>Student</th><td>{{ $payment->student->name }}</td></tr>
        <tr><th>Student ID</th><td>{{ $payment->student->student_id_number }}</td></tr>
        <tr><th>Payment Method</th><td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td></tr>
        @if ($payment->reference_number)
            <tr><th>Reference Number</th><td>{{ $payment->reference_number }}</td></tr>
        @endif
        <tr><th>Status</th><td>{{ ucfirst($payment->status) }}</td></tr>
    </table>

    <h3>Payment Details</h3>
    <table>
        <thead>
            <tr><th>Service</th><th class="text-right">Amount Paid</th></tr>
        </thead>
        <tbody>
            @foreach ($payment->allocations as $allocation)
                <tr>
                    <td>{{ $allocation->label() }}</td>
                    <td class="text-right">₦{{ number_format($allocation->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Paid</td>
                <td class="text-right">₦{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Current Balance</h3>
    <table>
        <tbody>
            @foreach ($balances as $balance)
                <tr>
                    <td>{{ $balance['label'] }} Balance</td>
                    <td class="text-right">₦{{ number_format($balance['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Outstanding</td>
                <td class="text-right">₦{{ number_format($balances->sum('balance'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>{{ $payment->recordedBy?->name }}<br>Secretary Signature</td>
            <td>&nbsp;<br>Student's Signature</td>
        </tr>
    </table>

    <p class="no-refund">All payments are final. No refunds after payment.</p>
</body>
</html>
