<?php

/**
 * Helper Functions
 *
 * Global utility functions available throughout the application
 */

/**
 * Escape HTML output to prevent XSS attacks
 *
 * @param string|null $string The string to escape
 * @return string The escaped string
 */
function e($string): string
{
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Get configuration value with optional default
 *
 * @param string $key The configuration key name
 * @param mixed $default Default value if not found
 * @return mixed
 */
function env(string $key, $default = null)
{
    static $config = null;

    // Load config file once
    if ($config === null) {
        $configFile = __DIR__ . '/../config/env.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
        } else {
            $config = [];
        }
    }

    return $config[$key] ?? $default;
}

/**
 * Get base URL for the application
 *
 * @param string $path Path to append to base URL
 * @return string
 */
function url(string $path = ''): string
{
    $baseUrl = rtrim(env('APP_URL', ''), '/');
    $path = ltrim($path, '/');

    return $path ? $baseUrl . '/' . $path : $baseUrl;
}

/**
 * Get asset URL
 *
 * @param string $path Path to asset
 * @return string
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Check if we're in debug mode
 *
 * @return bool
 */
function isDebug(): bool
{
    return env('APP_DEBUG', false) === true;
}

/**
 * Simple logger function
 *
 * @param string $message
 * @param string $level
 * @param array $context
 */
function logMessage(string $message, string $level = 'INFO', array $context = []): void
{
    $logFile = __DIR__ . '/../storage/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logLine = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";

    file_put_contents($logFile, $logLine, FILE_APPEND);
}

/**
 * Redirect to a URL
 *
 * @param string $url
 * @param int $statusCode
 * @return never
 */
function redirect(string $url, int $statusCode = 302): never
{
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Get old input value from session (for form repopulation after validation errors)
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function old(string $key, $default = '')
{
    return $_SESSION['_old_input'][$key] ?? $default;
}

/**
 * Flash a message to the session
 *
 * @param string $key
 * @param mixed $value
 */
function flash(string $key, $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

/**
 * Get a flashed message from session
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getFlash(string $key, $default = null)
{
    $value = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);
    return $value;
}
