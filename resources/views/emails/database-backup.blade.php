@component('mail::message')
# CDSMS Database Backup

A backup of the Classic Driving School Management System database was generated on **{{ $date }}**.

The attached file contains every student, payment, course, and other school record as of this date. Keep it somewhere safe — it can be used to restore the system's data if it's ever lost.

@component('mail::panel')
This is an automated message. You don't need to reply.
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
