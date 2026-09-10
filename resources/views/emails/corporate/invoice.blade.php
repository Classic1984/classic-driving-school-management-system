@component('mail::message')
Dear {{ $companyName }},

Please find attached your invoice for the training programme.

**Amount Due: ₦{{ $amountDue }}**

Kindly review the attached invoice and proceed with payment using the payment details provided.

Thank you for choosing Classic Driving School.

Classic Driving School
@endcomponent
