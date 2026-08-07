<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Finance Summary {{ $year }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 11px; font-weight: normal; color: #6b7280; margin-top: 4px; }
        h3 { font-size: 14px; margin-top: 24px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; font-size: 10px; }
        tfoot td { font-weight: bold; border-top: 2px solid #9ca3af; }
        .balance-negative { color: #dc2626; }
        .balance-positive { color: #16a34a; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Finance Summary — {{ $year }} — Generated {{ now()->format('Y-m-d H:i') }}</h2>

    <h3>Income, Expenses &amp; Balance</h3>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-right">Income (₦)</th>
                <th class="text-right">Expenses (₦)</th>
                <th class="text-right">Balance (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($months as $month)
                <tr>
                    <td>{{ $month['label'] }}</td>
                    <td class="text-right">{{ number_format($month['income'], 2) }}</td>
                    <td class="text-right">{{ number_format($month['expenses'], 2) }}</td>
                    <td class="text-right {{ $month['balance'] < 0 ? 'balance-negative' : 'balance-positive' }}">
                        {{ number_format($month['balance'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Year Total</td>
                <td class="text-right">{{ number_format($totals['income'], 2) }}</td>
                <td class="text-right">{{ number_format($totals['expenses'], 2) }}</td>
                <td class="text-right {{ $totals['balance'] < 0 ? 'balance-negative' : 'balance-positive' }}">
                    {{ number_format($totals['balance'], 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    @if ($discounts->isNotEmpty())
        <h3>Discounts — {{ $year }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th class="text-right">Original Fee (₦)</th>
                    <th class="text-right">Discount (₦)</th>
                    <th class="text-right">Final Fee (₦)</th>
                    <th>Reason</th>
                    <th>Approved By</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($discounts as $enrollment)
                    <tr>
                        <td>{{ $enrollment->student->name }}</td>
                        <td>{{ $enrollment->course->name }}</td>
                        <td class="text-right">{{ number_format($enrollment->originalFee(), 2) }}</td>
                        <td class="text-right">{{ number_format($enrollment->discount_amount, 2) }}</td>
                        <td class="text-right">{{ number_format($enrollment->fee(), 2) }}</td>
                        <td>{{ config("discounts.reasons.{$enrollment->discount_reason}", $enrollment->discount_reason) }}</td>
                        <td>{{ $enrollment->discountApprovedBy?->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total Discounted</td>
                    <td class="text-right">{{ number_format($discounts->sum('discount_amount'), 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    @endif
</body>
</html>
