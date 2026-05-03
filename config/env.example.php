<?php

/**
 * Application Configuration Template
 *
 * Copy this file to env.php and update with your settings
 * cp env.example.php env.php
 */

return [
    // Application Settings
    'APP_ENV' => 'production',
    'APP_DEBUG' => false,
    'APP_URL' => 'https://your-domain.com',
    'APP_TIMEZONE' => 'Europe/Bratislava',

    // Database Configuration
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'your_database',
    'DB_USER' => 'your_user',
    'DB_PASS' => 'your_password',
    'DB_CHARSET' => 'utf8mb4',

    // Session Configuration
    'SESSION_LIFETIME' => 7200,
    'SESSION_NAME' => 'screeps_session',
    'SESSION_SECURE' => true, // Set to true if using HTTPS

    // Security Settings
    'CSRF_TOKEN_LIFETIME' => 3600,

    // Rate Limiting
    'RATE_LIMIT_LOGIN_ATTEMPTS' => 5,
    'RATE_LIMIT_LOGIN_WINDOW' => 900, // 15 minutes

    // Email Configuration (optional - for password reset)
    'MAIL_HOST' => 'smtp.example.com',
    'MAIL_PORT' => 587,
    'MAIL_USERNAME' => 'your_email@example.com',
    'MAIL_PASSWORD' => 'your_email_password',
    'MAIL_FROM' => 'noreply@example.com',
    'MAIL_FROM_NAME' => 'Your App Name',
    'MAIL_ENCRYPTION' => 'tls',
];
