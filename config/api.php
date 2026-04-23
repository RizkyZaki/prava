<?php

return [
    'version' => env('API_VERSION', 'v1'),
    'prefix' => env('API_PREFIX', 'api'),
    'throttle' => [
        'default' => env('API_THROTTLE_DEFAULT', '60,1'),
        'auth' => env('API_THROTTLE_AUTH', '10,1'),
        'heavy' => env('API_THROTTLE_HEAVY', '20,1'),
    ],
    'token_expiry_days' => env('API_TOKEN_EXPIRY', 30),
];
