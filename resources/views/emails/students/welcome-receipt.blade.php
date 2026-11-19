@component('mail::message')
Dear {{ $studentName }},

Thank you for registering with Classic Driving School! We're glad to have you with us.

Please find attached your official payment receipt.

**Amount Paid: ₦{{ $amountPaid }}**
**Receipt No.: {{ $receiptNumber }}**

Keep this receipt for your records. If you have any questions, please don't hesitate to reach out to us.

Safety. Skill. Confidence.

Classic Driving School
@endcomponent
