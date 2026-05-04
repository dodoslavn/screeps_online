#!/bin/bash

#
# Config Generator for Screeps Online
# Usage: ./generate-config.sh
#

CONFIG_FILE="config/env.php"
EXAMPLE_FILE="config/env.example.php"

echo "=== Screeps Online Config Generator ==="
echo ""

# Check if config already exists
if [ -f "$CONFIG_FILE" ]; then
    read -p "Config file already exists. Overwrite? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Cancelled."
        exit 0
    fi
fi

# Prompt for values
echo "Enter your configuration values:"
echo ""

read -p "App URL (e.g., https://screeps.dodoslav.eu): " APP_URL
read -p "Database Host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Database Name: " DB_NAME
read -p "Database User: " DB_USER
read -sp "Database Password: " DB_PASS
echo ""

read -p "Use HTTPS? (y/N): " -n 1 -r USE_HTTPS
echo ""
if [[ $USE_HTTPS =~ ^[Yy]$ ]]; then
    SESSION_SECURE="true"
else
    SESSION_SECURE="false"
fi

read -p "Enable Debug Mode? (y/N): " -n 1 -r DEBUG_MODE
echo ""
if [[ $DEBUG_MODE =~ ^[Yy]$ ]]; then
    APP_DEBUG="true"
    APP_ENV="development"
else
    APP_DEBUG="false"
    APP_ENV="production"
fi

# Generate config file
cat > "$CONFIG_FILE" << EOF
<?php

/**
 * Application Configuration
 *
 * Generated on $(date)
 */

return [
    // Application Settings
    'APP_ENV' => '$APP_ENV',
    'APP_DEBUG' => $APP_DEBUG,
    'APP_URL' => '$APP_URL',
    'APP_TIMEZONE' => 'Europe/Bratislava',

    // Database Configuration
    'DB_HOST' => '$DB_HOST',
    'DB_NAME' => '$DB_NAME',
    'DB_USER' => '$DB_USER',
    'DB_PASS' => '$DB_PASS',
    'DB_CHARSET' => 'utf8mb4',

    // Session Configuration
    'SESSION_LIFETIME' => 7200,
    'SESSION_NAME' => 'screeps_session',
    'SESSION_SECURE' => $SESSION_SECURE,

    // Security Settings
    'CSRF_TOKEN_LIFETIME' => 3600,

    // Rate Limiting
    'RATE_LIMIT_LOGIN_ATTEMPTS' => 5,
    'RATE_LIMIT_LOGIN_WINDOW' => 900,

    // Email Configuration (optional - for password reset)
    'MAIL_HOST' => 'smtp.fordo.sk',
    'MAIL_PORT' => 587,
    'MAIL_USERNAME' => 'screeps@fordo.sk',
    'MAIL_PASSWORD' => '',
    'MAIL_FROM' => 'screeps@fordo.sk',
    'MAIL_FROM_NAME' => 'Screeps Online',
    'MAIL_ENCRYPTION' => 'tls',
];
EOF

# Set permissions
chmod 600 "$CONFIG_FILE"

echo ""
echo "✅ Config file created: $CONFIG_FILE"
echo "✅ Permissions set to 600 (owner read/write only)"
echo ""
echo "Next steps:"
echo "1. Review: cat $CONFIG_FILE"
echo "2. Run migrations: php database/migrate.php"
echo "3. Test connection: php -r \"require 'vendor/autoload.php'; use ScreepsOnline\\Models\\Database; Database::getInstance();\""
