<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        table { border-collapse: collapse; width: 100%; }
        .header { text-align: center; border-bottom: 3px solid #000000; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #000000; margin: 8px 0 0; }
        .header p { color: #6b7280; margin: 4px 0 0; font-size: 11px; }
        .rows td { padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 12px; }
        .rows .label { color: #6b7280; font-weight: bold; }
        .rows .value { text-align: right; }
        .amount { font-size: 18px; font-weight: bold; color: #000000; }
        .status-paid { color: #15803d; font-weight: bold; }
        .footer { margin-top: 24px; text-align: center; font-size: 10px; color: #9ca3af; }
        .footer .website { color: #b45309; font-weight: bold; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAYMENT RECEIPT</h1>
        <p>{{ strtoupper($settings->company_name ?: 'CLASSIC DRIVING SCHOOL') }}</p>
    </div>

    <table class="rows">
        <tr><td class="label">Received From</td><td class="value">{{ $payment->invoice->company->name }}</td></tr>
        @if ($payment->invoice->programme_name)
            <tr><td class="label">Programme</td><td class="value">{{ $payment->invoice->programme_name }}</td></tr>
        @endif
        <tr><td class="label">Invoice No.</td><td class="value">{{ $payment->invoice->invoice_number }}</td></tr>
        <tr><td class="label">Amount Paid</td><td class="value amount">₦{{ number_format((float) $payment->amount, 0) }}</td></tr>
        <tr><td class="label">Payment Method</td><td class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td></tr>
        @if ($payment->transaction_reference)
            <tr><td class="label">Transaction Reference</td><td class="value">{{ $payment->transaction_reference }}</td></tr>
        @endif
        <tr><td class="label">Payment Date</td><td class="value">{{ $payment->payment_date->format('d F Y') }}</td></tr>
        <tr><td class="label">Status</td><td class="value status-paid">PAID</td></tr>
        <tr><td class="label">Receipt No.</td><td class="value">{{ $payment->receipt_number }}</td></tr>
    </table>

    <p class="footer">
        Thank you for choosing {{ $settings->company_name ?: 'Classic Driving School' }}.
        @if ($settings->website)
            <br><span class="website">{{ $settings->website }}</span>
        @endif
    </p>
</body>
</html>
