<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, PayMongo, and more.
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

    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
    ],

    'ml' => [
        'token' => env('ML_API_TOKEN', 'camp_freedive_ml_secret_token_2026'),
        'url' => env('ML_SERVICE_URL', 'http://127.0.0.1:8001'),
    ],

    'ml_safety' => [
        'url' => env('ML_SAFETY_SERVICE_URL', 'http://127.0.0.1:8001'),
        'timeout' => (int) env('ML_SAFETY_TIMEOUT', 4),
        'enabled' => (bool) env('ML_SAFETY_ENABLED', true),
    ],

];
