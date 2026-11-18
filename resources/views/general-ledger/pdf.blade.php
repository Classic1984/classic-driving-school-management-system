<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger - {{ $year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        h3 { font-size: 14px; margin-top: 24px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
        tfoot td { font-weight: bold; border-top: 2px solid #9ca3af; }
        .text-right { text-align: right; }
        .status { margin-top: 4px; font-size: 11px; font-weight: bold; }
        .status.balanced { color: #15803d; }
        .status.unbalanced { color: #dc2626; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>General Ledger — {{ $year }} — Generated {{ now()->format('l, M j, Y g:i A') }}</h2>

    <h3>Trial Balance</h3>
    <p class="status {{ $trialBalance['balanced'] ? 'balanced' : 'unbalanced' }}">
        {{ $trialBalance['balanced'] ? 'Balanced' : 'Out of Balance' }}
    </p>
    <table>
        <thead>
            <tr><th>Account</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr>
        </thead>
        <tbody>
            @forelse ($trialBalance['accounts'] as $account => $amounts)
                <tr>
                    <td>{{ $account }}</td>
                    <td class="text-right">{{ $amounts['debit'] > 0 ? '₦'.number_format($amounts['debit'], 2) : '—' }}</td>
                    <td class="text-right">{{ $amounts['credit'] > 0 ? '₦'.number_format($amounts['credit'], 2) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No paid payments or expenses recorded for {{ $year }}.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="text-right">₦{{ number_format($trialBalance['total_debit'], 2) }}</td>
                <td class="text-right">₦{{ number_format($trialBalance['total_credit'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Ledger Entries</h3>
    <table>
        <thead>
            <tr><th>Date</th><th>Reference</th><th>Description</th><th>Debit</th><th>Credit</th><th class="text-right">Amount</th></tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td>{{ $entry['date']->format('M j, Y') }}</td>
                    <td>{{ $entry['reference'] }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td>{{ $entry['debit_account'] }}</td>
                    <td>{{ $entry['credit_account'] }}</td>
                    <td class="text-right">₦{{ number_format($entry['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No ledger entries for {{ $year }}.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
