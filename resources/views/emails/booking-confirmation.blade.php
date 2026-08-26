@component('mail::message')
Dear {{ $lead->name }},

Thank you for booking your driving training programme with Classic Driving School.

We are pleased to confirm that we have received your booking request successfully.

## Booking Details

@component('mail::panel')
**Programme:** {{ $programmeName }}<br>
**Duration:** {{ $duration }}<br>
**Preferred Start Date:** {{ $startDate }}<br>
**Training Type:** {{ $trainingType }}
@endcomponent

Our team will review your booking and contact you shortly with confirmation of your training schedule and any further information required.

If you have any questions or need to make changes to your booking, please contact Classic Driving School on 0806 887 8663 / 0809 476 0609 or reply to this email.

Thank you for choosing Classic Driving School.

**When you say Classic, you say it all.**

Best regards,<br>
Classic Driving School<br>
Professional Driver Training & Defensive Driving
@endcomponent
