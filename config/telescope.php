<?php

return [
    'enabled' => env('TELESCOPE_ENABLED', false),

    'path' => env('TELESCOPE_PATH', 'telescope'),

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


