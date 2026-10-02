<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Application Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the Service Account JSON file.
    |
    */
    'application_credentials' => env('GOOGLE_APPLICATION_CREDENTIALS', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Google Client Config
    |--------------------------------------------------------------------------
    */
    'config' => [
        'client_id' => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'api_key' => env('GOOGLE_API_KEY', ''),
        'client_credentials_pass_in_body' => true,
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
    
    /*
    |--------------------------------------------------------------------------
    | Additional Access Config
    |--------------------------------------------------------------------------
    */
    'access_type' => 'offline',
    'approval_prompt' => 'force',
    'prompt' => 'consent',
];
