@component('mail::message')
Dear {{ $studentName }},

Thank you for registering with Classic Driving School! We're glad to have you with us.

Please find attached your official payment receipt.

**Amount Paid: ₦{{ $amountPaid }}**
**Receipt No.: {{ $receiptNumber }}**

@if ($outstanding->isNotEmpty())
You still have an outstanding balance:

@foreach ($outstanding as $balance)
- {{ $balance['label'] }}: ₦{{ number_format($balance['balance'], 2) }}@if ($balance['due_date']) (due by {{ $balance['due_date']->format('M j, Y') }})@endif

@endforeach
@endif

Keep this receipt for your records. If you have any questions, please don't hesitate to reach out to us.

Safety. Skill. Confidence.

Classic Driving School
@endcomponent
