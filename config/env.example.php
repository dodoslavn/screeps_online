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
        'email' => 'screeps@dodoslav.eu',  // Contact email (displayed on site)
        'year' => '2019',
    ],

    // Session Configuration
    'session' => [
        'secure' => true,      // Set to true if using HTTPS (recommended)
        'httponly' => true,    // Protect against XSS
        'samesite' => 'Lax',   // CSRF protection
        'lifetime' => 7200,    // Session lifetime in seconds (2 hours)
    ],
];
