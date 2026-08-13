<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'termii' => [
        'api_key' => env('TERMII_API_KEY'),
        'sender_id' => env('TERMII_SENDER_ID', 'N-Alert'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
        // Twilio Content Template SIDs (HXxxxxxxxx...), created and
        // approved in the Twilio console - required because these
        // reminders are business-initiated, not replies. Left blank,
        // WhatsApp sending for that reminder is silently skipped.
        'whatsapp_templates' => [
            'balance_reminder' => env('TWILIO_WHATSAPP_BALANCE_REMINDER_TEMPLATE_SID'),
            'theory_class_reminder' => env('TWILIO_WHATSAPP_THEORY_REMINDER_TEMPLATE_SID'),
            'theory_class_cancellation' => env('TWILIO_WHATSAPP_THEORY_CANCELLATION_TEMPLATE_SID'),
            'lead_follow_up' => env('TWILIO_WHATSAPP_LEAD_FOLLOWUP_TEMPLATE_SID'),
        ],
    ],

];
