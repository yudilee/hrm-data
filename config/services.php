<?php

declare(strict_types=1);

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

    /*
    |--------------------------------------------------------------------------
    | Odoo Integration
    |--------------------------------------------------------------------------
    |
    | Security credentials for the Odoo ↔ RTS labour code integration.
    | - shared_secret: HMAC key for verifying signed launch URLs from Odoo
    | - webhook_secret: HMAC key for signing webhook callbacks to Odoo
    | - allowed_callback_hosts: comma-separated list of allowed Odoo domains
    | - url_expiry_seconds: how long a signed launch URL remains valid
    |
    */
    'odoo' => [
        'shared_secret' => env('ODOO_SHARED_SECRET'),
        'webhook_secret' => env('ODOO_WEBHOOK_SECRET'),
        'allowed_callback_hosts' => array_map('trim', array_filter(explode(',', env('ODOO_ALLOWED_CALLBACK_HOSTS', '')))),
        'url_expiry_seconds' => (int) env('ODOO_URL_EXPIRY_SECONDS', 300),
    ],

];
