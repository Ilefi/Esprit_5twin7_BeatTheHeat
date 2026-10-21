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

    // Public API, no key needed: https://openfoodfacts.github.io/openfoodfacts-server/api/
    'openfoodfacts' => [
        'url' => env('OPENFOODFACTS_URL', 'https://world.openfoodfacts.org'),
        'search_url' => env('OPENFOODFACTS_SEARCH_URL', 'https://search.openfoodfacts.org'),
        'user_agent' => env('OPENFOODFACTS_USER_AGENT', 'NutriTrace/1.0 (Esprit academic project)'),
        'timeout' => (int) env('OPENFOODFACTS_TIMEOUT', 5),
    ],

    // Google Gemini (free key: https://aistudio.google.com/apikey). Without a key the AI buttons explain how to enable them.
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.8-flash'),
        'url' => env('GEMINI_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 20),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
