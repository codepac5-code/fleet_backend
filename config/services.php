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

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'public' => env('STRIPE_PUBLIC'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'overage_billing' => env('STRIPE_OVERAGE_BILLING', false),
    ],

    'smsala' => [
        'username' => env('SMSALA_USERNAME'),
        'password' => env('SMSALA_PASSWORD'),
        'api_id' => env('SMSALA_API_ID'),
    ],
    

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_KEY'),
    ],

    'whatsapp' => [
        'base_url' => env('WHATSAPP_API_URL', 'https://message.dashboard.technoplus.tech'),
        'prefix' => env('WHATSAPP_API_PREFIX', 'whatsapp/api/v1'),
        'token' => env('WHATSAPP_API_TOKEN'),
        'session_id' => env('WHATSAPP_SESSION_ID'),
    ],

    'realtime' => [
        'url' => rtrim(env('SOCKET_IO_SERVER_Host', 'http://127.0.0.1'), '/') . ':' . env('SOCKET_IO_SERVER_Port', 3000),
        'order_board' => env('REALTIME_ORDER_BOARD', true),
        // The NEW rt:* gateway (realtime-gateway/server.js, default port 6002),
        // consumed by the panel alongside the legacy order-board socket above.
        // Public browser-reachable URL; empty disables the panel live layer.
        'gateway_url' => env('FLEET_RT_URL', ''),
    ],

];
