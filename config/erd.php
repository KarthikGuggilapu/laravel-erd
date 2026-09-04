<?php

return [
    'enabled' => env(
        'ERD_ENABLED',
        env('APP_ENV', 'production') !== 'production'
    ),

    'route' => [
        'prefix' => 'erd',
        'middleware' => [],
    ],

    'storage' => [
        'path' => storage_path('erd'),
    ],
];