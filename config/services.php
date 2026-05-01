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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],


    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN_PRODUCAO', env('MERCADOPAGO_ACCESS_TOKEN')),
        'access_token_test' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
        'webhook_url' => env('MERCADOPAGO_WEBHOOK_URL', env('APP_URL').'/api/webhooks/mercadopago'),
        'environment' => env('MERCADOPAGO_AMBIENTE', 'production'),
        'base_url' => env('MERCADOPAGO_API_BASE_URL', 'https://api.mercadopago.com'),
    ],

    'sentry' => [
        'dsn' => env('SENTRY_LARAVEL_DSN'),
        'enabled' => env('SENTRY_ENABLED', false),
        'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),
        'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0),
    ],

    'elastic' => [
        'enabled' => env('ELASTIC_LOG_ENABLED', false),
        'host' => env('ELASTIC_LOG_HOST'),
        'index' => env('ELASTIC_LOG_INDEX', 'wave-runtime'),
    ],

];
