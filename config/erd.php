<?php

return [
    'enabled' => env(
        'ERD_ENABLED',
        !app()->environment('production')
    ),

    'route' => [
        'prefix' => 'erd',
        'middleware' => [],
    ],

    'storage' => [
        'path' => storage_path('erd'),
    ],
];