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

    'whish' => [
        'channel' => env('WHISH_CHANNEL'),
        'secret' => env('WHISH_SECRET'),
        'website_url' => env('WHISH_WEBSITE_URL'),
        'env' => env('WHISH_ENV', 'sandbox'),
    ],

    'mpgs' => [
        'merchant_id'  => env('MPGS_MERCHANT_ID', 'TEST06300200'),
        'api_password' => env('MPGS_API_PASSWORD'),
        'gateway_url'  => env('MPGS_GATEWAY_URL', 'https://creditlibanais-netcommerce.gateway.mastercard.com/'),
        'api_version'  => env('MPGS_API_VERSION', '61'),
    ],

];
