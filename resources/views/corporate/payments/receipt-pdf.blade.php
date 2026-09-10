<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; }
        table { border-collapse: collapse; width: 100%; }
        .header { text-align: center; padding-bottom: 12px; border-bottom: 2px solid #fbbf24; }
        .header h1 { font-size: 28px; color: #b45309; margin: 8px 0 0; }
        .header p { color: #000000; font-weight: bold; letter-spacing: 1px; margin: 4px 0 0; font-size: 11px; }
        .rows { margin-top: 16px; border: 1px solid #fde68a; border-radius: 8px; }
        .rows td { padding: 8px 10px; border-bottom: 1px solid #fef3c7; font-size: 12px; }
        .rows .label { color: #6b7280; font-weight: bold; }
        .rows .value { text-align: right; }
        .amount-row td { background: #000000; color: #fbbf24; }
        .amount { font-size: 18px; font-weight: bold; }
        .status-paid { color: #15803d; font-weight: bold; }
        .footer { margin-top: 24px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .footer .thanks { font-size: 18px; font-style: italic; font-weight: bold; color: #111827; }
        .footer .website { color: #b45309; font-weight: bold; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <p>{{ strtoupper($settings->company_name ?: 'CLASSIC DRIVING SCHOOL') }}</p>
        <h1>PAYMENT RECEIPT</h1>
    </div>

    <table class="rows">
        <tr><td class="label">Received From</td><td class="value">{{ $payment->invoice->company->name }}</td></tr>
        @if ($payment->invoice->programme_name)
            <tr><td class="label">Programme</td><td class="value">{{ $payment->invoice->programme_name }}</td></tr>
        @endif
        <tr><td class="label">Invoice No.</td><td class="value">{{ $payment->invoice->invoice_number }}</td></tr>
        <tr class="amount-row"><td class="label" style="color:#fbbf24;">Amount Paid</td><td class="value amount">₦{{ number_format((float) $payment->amount, 0) }}</td></tr>
        <tr><td class="label">Payment Method</td><td class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td></tr>
        @if ($payment->transaction_reference)
            <tr><td class="label">Transaction Reference</td><td class="value">{{ $payment->transaction_reference }}</td></tr>
        @endif
        <tr><td class="label">Payment Date</td><td class="value">{{ $payment->payment_date->format('d F Y') }}</td></tr>
        <tr><td class="label">Status</td><td class="value status-paid">PAID</td></tr>
        <tr><td class="label">Receipt No.</td><td class="value">{{ $payment->receipt_number }}</td></tr>
    </table>

    <p class="footer">
        <span class="thanks">Thank You!</span><br>
        Thank you for choosing {{ $settings->company_name ?: 'Classic Driving School' }}.
        @if ($settings->website)
            <br><span class="website">{{ $settings->website }}</span>
        @endif
    </p>
</body>
</html>
