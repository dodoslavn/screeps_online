<?php

return [
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 7200),
        'name' => env('SESSION_NAME', 'screeps_session'),
        'secure' => env('SESSION_SECURE', false),
        'httponly' => true,
        'samesite' => 'Strict',
    ],

    'csrf' => [
        'token_lifetime' => env('CSRF_TOKEN_LIFETIME', 3600),
    ],

    'rate_limit' => [
        'login_attempts' => env('RATE_LIMIT_LOGIN_ATTEMPTS', 5),
        'login_window' => env('RATE_LIMIT_LOGIN_WINDOW', 900), // 15 minutes
    ],

    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_special' => false,
    ],
];
