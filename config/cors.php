<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Only the public lead-capture endpoint needs this - the marketing site
    | (classicdriving.com.ng) posts to it directly from the browser, from a
    | different origin than this app. Everything else here is a session-
    | authenticated same-origin form post and never needs CORS.
    |
    */

    'paths' => ['public/leads'],

    'allowed_methods' => ['POST'],

    'allowed_origins' => array_filter([
        env('MARKETING_SITE_URL', 'https://classicdriving.com.ng'),
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
