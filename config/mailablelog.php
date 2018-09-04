<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subject format
    |--------------------------------------------------------------------------
    |
    | Set the default subject format for your mailable logs. Please checkout
    | the monolog format for more information
    |
    */
    'subject_format' => env('MAILABLE_LOG_SUBJECT_FORMAT', '[%datetime%] %level_name%: %message%'),

    /*
    |--------------------------------------------------------------------------
    | From information
    |--------------------------------------------------------------------------
    |
    | Set default from information of your mailables. The from information
    | falls back to your mail config.
    |
    */
    'from' => [
        'address' => env('MAILABLE_LOG_FROM_ADDRESS'),
        'name'    => env('MAILABLE_LOG_FROM_NAME'),
    ],
];
