# Running Without Composer

The application uses only 2 external dependencies:
- `phpdotenv` - For loading .env files
- `phpmailer` - For sending password reset emails

## Option 1: Manual Dependency Installation

Download the libraries manually and skip Composer:

### 1. Download phpdotenv

```bash
mkdir -p vendor/vlucas/phpdotenv/src
cd vendor/vlucas/phpdotenv
wget https://github.com/vlucas/phpdotenv/archive/refs/tags/v5.6.0.tar.gz
tar -xzf v5.6.0.tar.gz --strip-components=1
```

### 2. Download phpmailer

```bash
mkdir -p vendor/phpmailer/phpmailer/src
cd vendor/phpmailer/phpmailer
wget https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.tar.gz
tar -xzf v6.9.1.tar.gz --strip-components=1
```

### 3. Create Manual Autoloader

Create `vendor/autoload.php`:

```php
<?php

// Manual autoloader for non-composer setup
spl_autoload_register(function ($class) {
    // ScreepsOnline namespace
    if (strpos($class, 'ScreepsOnline\\') === 0) {
        $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 14)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
    
    // Dotenv namespace
    if (strpos($class, 'Dotenv\\') === 0) {
        $file = __DIR__ . '/vlucas/phpdotenv/src/' . str_replace('\\', '/', substr($class, 7)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
    
    // PHPMailer namespace
    if (strpos($class, 'PHPMailer\\') === 0) {
        $file = __DIR__ . '/phpmailer/phpmailer/src/' . str_replace('\\', '/', substr($class, 10)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Load helpers
require __DIR__ . '/../src/helpers.php';
```

## Option 2: Remove phpdotenv Entirely

The .env file is just for configuration. We can replace it with a simple config file:

### 1. Create config/env.php

```php
<?php
return [
    'APP_ENV' => 'production',
    'APP_DEBUG' => false,
    'APP_URL' => 'https://screeps.online',
    'APP_TIMEZONE' => 'Europe/Bratislava',
    
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'screepsdb',
    'DB_USER' => 'screepsdb',
    'DB_PASS' => 'your_password_here',
    'DB_CHARSET' => 'utf8mb4',
    
    'SESSION_LIFETIME' => 7200,
    'SESSION_NAME' => 'screeps_session',
    'SESSION_SECURE' => true,
    
    'CSRF_TOKEN_LIFETIME' => 3600,
    
    'RATE_LIMIT_LOGIN_ATTEMPTS' => 5,
    'RATE_LIMIT_LOGIN_WINDOW' => 900,
];
```

### 2. Update src/helpers.php

Replace the `env()` function:

```php
function env(string $key, $default = null) {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/env.php';
    }
    return $config[$key] ?? $default;
}
```

### 3. Update src/App.php

Remove Dotenv usage:

```php
// REMOVE:
use Dotenv\Dotenv;

private function loadEnvironment(): void
{
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// REPLACE WITH:
private function loadEnvironment(): void
{
    // Configuration loaded via env() helper
}
```

### 4. Remove phpmailer

If you don't need password reset emails, remove phpmailer and disable those routes in routes.php.

## Option 3: Use Native PHP for Everything

Complete removal of external dependencies:

1. **Configuration** → Use `config/env.php` (native PHP array)
2. **Email** → Use PHP's `mail()` function or disable password reset
3. **Autoloading** → Manual `spl_autoload_register()`

This makes the app **100% self-contained** with zero dependencies!

## Recommendation

For production, I recommend **Option 2**:
- No Composer needed
- Simple PHP config file
- Password reset via direct email (or disable it)
- Zero external dependencies

This keeps the simplicity you want while maintaining security.
