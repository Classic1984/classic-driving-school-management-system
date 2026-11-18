<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Statements - {{ $year }}</title>
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
        .net-income { margin-top: 12px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Financial Statements — {{ $year }} — Generated {{ now()->format('l, M j, Y g:i A') }}</h2>

    <h3>Income Statement — {{ $year }}</h3>
    <table>
        <thead><tr><th>Revenue</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
            @forelse ($incomeStatement['revenue'] as $account => $amount)
                <tr><td>{{ $account }}</td><td class="text-right">₦{{ number_format($amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="2">No revenue recorded.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td>Total Revenue</td><td class="text-right">₦{{ number_format($incomeStatement['total_revenue'], 2) }}</td></tr></tfoot>
    </table>

    <table>
        <thead><tr><th>Expenses</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
            @forelse ($incomeStatement['expenses'] as $account => $amount)
                <tr><td>{{ $account }}</td><td class="text-right">₦{{ number_format($amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="2">No expenses recorded.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td>Total Expenses</td><td class="text-right">₦{{ number_format($incomeStatement['total_expenses'], 2) }}</td></tr></tfoot>
    </table>

    <p class="net-income">Net Income: ₦{{ number_format($incomeStatement['net_income'], 2) }}</p>

    <h3>Balance Sheet — as of {{ $asOfDate }}</h3>
    <p class="status {{ $balanceSheet['balanced'] ? 'balanced' : 'unbalanced' }}">
        {{ $balanceSheet['balanced'] ? 'Balanced' : 'Out of Balance' }}
    </p>

    <table>
        <thead><tr><th>Assets</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
            @foreach ($balanceSheet['assets'] as $account => $amount)
                <tr><td>{{ $account }}</td><td class="text-right">₦{{ number_format($amount, 2) }}</td></tr>
            @endforeach
        </tbody>
        <tfoot><tr><td>Total Assets</td><td class="text-right">₦{{ number_format($balanceSheet['total_assets'], 2) }}</td></tr></tfoot>
    </table>

    <table>
        <thead><tr><th>Liabilities</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
            <tr><td colspan="2">None tracked.</td></tr>
        </tbody>
        <tfoot><tr><td>Total Liabilities</td><td class="text-right">₦{{ number_format($balanceSheet['total_liabilities'], 2) }}</td></tr></tfoot>
    </table>

    <table>
        <thead><tr><th>Equity</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
            @foreach ($balanceSheet['equity'] as $account => $amount)
                <tr><td>{{ $account }}</td><td class="text-right">₦{{ number_format($amount, 2) }}</td></tr>
            @endforeach
        </tbody>
        <tfoot><tr><td>Total Equity</td><td class="text-right">₦{{ number_format($balanceSheet['total_equity'], 2) }}</td></tr></tfoot>
    </table>
</body>
</html>
