<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google application name
    |--------------------------------------------------------------------------
    */
    'application_name' => env('GOOGLE_APPLICATION_NAME', 'Dashboard HSE'),

    /*
    |--------------------------------------------------------------------------
    | Google service account
    |--------------------------------------------------------------------------
    */
    'service' => [
        'enable' => env('GOOGLE_SERVICE_ENABLED', true),
        'file' => env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION', storage_path('app/google-credentials.json')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    'scopes' => [
        \Google\Service\Sheets::DRIVE,
        \Google\Service\Sheets::SPREADSHEETS,
    ],

    'access_type' => 'offline',
    'approval_prompt' => 'force',
    'prompt' => 'consent',
];
