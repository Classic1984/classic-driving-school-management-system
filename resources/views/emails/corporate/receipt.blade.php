@component('mail::message')
Dear {{ $companyName }},

Please find attached your payment receipt.

**Amount Paid: ₦{{ $amountPaid }}**

Thank you for choosing Classic Driving School.

Classic Driving School
@endcomponent
