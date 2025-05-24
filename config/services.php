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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'livekit' => [
        'host' => env('LIVEKIT_HOST'),
        'api_key' => env('LIVEKIT_API_KEY'),
        'api_secret' => env('LIVEKIT_API_SECRET'),
        'egress' => [
            's3' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'url' => env('AWS_URL'),
                'endpoint' => env('AWS_ENDPOINT'),
                'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            ],
            'r2' => [
                'key' => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
                'secret' => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
                'region' => env('CLOUDFLARE_R2_DEFAULT_REGION'),
                'bucket' => env('CLOUDFLARE_R2_BUCKET'),
                'url' => env('CLOUDFLARE_R2_URL'),
                'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
                'use_path_style_endpoint' => env('CLOUDFLARE_R2_USE_PATH_STYLE_ENDPOINT', false),
            ],
            'file_path' => 'local/{room_name}-{publisher_identity}-{time}',
        ],
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],
    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI'),
        'team_id' => env('APPLE_TEAM_ID'),
        'key_id' => env('APPLE_KEY_ID'),
        'key_file' => env('APPLE_KEY_FILE'), // Path to the .p8 file
    ],

];
