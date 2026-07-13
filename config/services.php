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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'shiprocket' => [
        'email' => env('SHIPROCKET_EMAIL'),
        'password' => env('SHIPROCKET_PASSWORD'),
        'pickup_location' => env('SHIPROCKET_PICKUP_LOCATION', 'Primary'),
        'pickup_pincode' => env('SHIPROCKET_PICKUP_PINCODE', '110001'),
        
        // Warehouse details for return deliveries (where returned products are sent)
        'warehouse_name' => env('SHIPROCKET_WAREHOUSE_NAME', 'Main Warehouse'),
        'warehouse_address' => env('SHIPROCKET_WAREHOUSE_ADDRESS', ''),
        'warehouse_city' => env('SHIPROCKET_WAREHOUSE_CITY', ''),
        'warehouse_state' => env('SHIPROCKET_WAREHOUSE_STATE', ''),
        'warehouse_phone' => env('SHIPROCKET_WAREHOUSE_PHONE', ''),
    ],

    'qikink' => [
        'client_id'     => env('QIKINK_CLIENT_ID', ''),
        'client_secret' => env('QIKINK_CLIENT_SECRET', ''),
        'sandbox'       => env('QIKINK_SANDBOX', true),
    ],

];
