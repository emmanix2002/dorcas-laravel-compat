<?php
return [
    // the client environment
    'env' => env('DORCAS_ENV', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | You need to provide the credentials that will be used while communicating
    | with the Dorcas API.
    |
    |
    */
    'client' => [

        // the client ID provided to you for use with your app
        'id' => env('DORCAS_CLIENT_ID', 0),

        // the client secret
        'secret' => env('DORCAS_CLIENT_SECRET', '')
    ]
];