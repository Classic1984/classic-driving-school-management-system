<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement - {{ $student->name }}</title>
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
        .line-through { text-decoration: line-through; color: #9ca3af; }
        .student-info { margin-top: 16px; }
        .student-info td { border: none; padding: 2px 8px 2px 0; }
        .student-info th { border: none; background: none; text-transform: none; font-size: 12px; font-weight: bold; color: #111827; width: 120px; }
        .no-refund { margin-top: 24px; text-align: center; font-size: 10px; font-weight: bold; color: #dc2626; }
    </style>
</head>
<body>
    <h1>Classic Driving School &amp; Son Nigeria Limited</h1>
    <h2>Student Financial Statement — Generated {{ now()->format('l, M j, Y g:i A') }}</h2>

    <table class="student-info">
        <tr><th>Student</th><td>{{ $student->name }}</td></tr>
        <tr><th>Student ID</th><td>{{ $student->student_id_number }}</td></tr>
        <tr><th>Phone</th><td>{{ $student->phone ?? '—' }}</td></tr>
    </table>

    <h3>Charges &amp; Balances</h3>
    <table>
        <thead>
            <tr><th>Item</th><th class="text-right">Price</th><th class="text-right">Paid</th><th class="text-right">Balance</th></tr>
        </thead>
        <tbody>
            @forelse ($financialOverview as $charge)
                <tr>
                    <td>{{ $charge['label'] }}</td>
                    <td class="text-right">₦{{ number_format($charge['price'], 2) }}</td>
                    <td class="text-right">₦{{ number_format($charge['paid'], 2) }}</td>
                    <td class="text-right">₦{{ number_format($charge['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No charges on file.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="text-right">₦{{ number_format($financialOverview->sum('price'), 2) }}</td>
                <td class="text-right">₦{{ number_format($financialOverview->sum('paid'), 2) }}</td>
                <td class="text-right">₦{{ number_format($financialOverview->sum('balance'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @php
        $discountedCourses = $student->courses->filter(fn ($course) => $course->pivot->hasDiscount());
    @endphp
    @if ($discountedCourses->isNotEmpty())
        <h3>Discounts Applied</h3>
        <table>
            <thead>
                <tr><th>Course</th><th class="text-right">Original Fee</th><th class="text-right">Discount</th><th class="text-right">Final Fee</th></tr>
            </thead>
            <tbody>
                @foreach ($discountedCourses as $course)
                    <tr>
                        <td>{{ $course->name }}</td>
                        <td class="text-right">₦{{ number_format($course->pivot->originalFee(), 2) }}</td>
                        <td class="text-right">{{ rtrim(rtrim(number_format((float) $course->pivot->discount_percentage, 2), '0'), '.') }}% (₦{{ number_format($course->pivot->discount_amount, 2) }})</td>
                        <td class="text-right">₦{{ number_format($course->pivot->fee(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>Payment History</h3>
    <table>
        <thead>
            <tr><th>Date</th><th>Receipt</th><th>Description</th><th class="text-right">Amount</th><th>Method</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($student->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('M j, Y') }}</td>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ $payment->description() }}</td>
                    <td class="text-right">₦{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                    <td>{{ ucfirst($payment->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No payments recorded.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total Paid</td>
                <td class="text-right">₦{{ number_format($student->payments->where('status', 'paid')->sum('amount'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <p class="no-refund">All payments are final. No refunds after payment.</p>
</body>
</html>
