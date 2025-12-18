<?php

return [
    'enabled' => env('TELESCOPE_ENABLED', false),

    'path' => env('TELESCOPE_PATH', 'telescope'),

    'allowed_emails' => env('TELESCOPE_ALLOWED_EMAILS', ''),

    'driver' => env('TELESCOPE_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],

    'middleware' => [
        'web',
    ],
];
