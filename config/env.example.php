<?php
/**
 * Environment Configuration Example
 *
 * Copy this file to env.php and update with your settings:
 *   cp config/env.example.php config/env.php
 *
 * Then edit config/env.php with your database credentials
 */

return [
    // Database Configuration
    'database' => [
        'host' => 'localhost',
        'name' => 'screeps_online',
        'user' => 'your_db_user',
        'password' => 'your_db_password',
    ],

    // Application Settings
    'app' => [
        'url' => 'https://screeps.dodoslav.eu',
        'name' => 'Screeps.dodoslav.eu',
        'timezone' => 'Europe/Bratislava',
    ],

    // Site Information
    'site' => [
        'author' => 'Dodoslav Novák',
        'email' => 'screeps@dodoslav.eu',
        'year' => '2019',
    ],

    // Mail Configuration (optional - for future features)
    'mail' => [
        'host' => 'smtp.dodoslav.eu',
        'port' => 587,
        'username' => 'screeps@dodoslav.eu',
        'password' => 'your_mail_password',
        'from_email' => 'screeps@dodoslav.eu',
        'from_name' => 'Screeps Server List',
    ],

    // Session Configuration
    'session' => [
        'secure' => true,      // Set to true if using HTTPS (recommended)
        'httponly' => true,    // Protect against XSS
        'samesite' => 'Lax',   // CSRF protection
        'lifetime' => 7200,    // Session lifetime in seconds (2 hours)
    ],
];
