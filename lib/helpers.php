<?php
/**
 * Helper Utilities
 *
 * Provides flash messages, redirects, and form helpers
 */

/**
 * Get configuration value
 *
 * @param string $key Dot notation key (e.g., 'site.author')
 * @param mixed $default Default value if not found
 * @return mixed
 */
function config($key, $default = null) {
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/../config/env.php';
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }

    return $value;
}

/**
 * Generate site footer HTML
 *
 * @return string
 */
function site_footer() {
    $email = config('site.email', '');
    $year = config('site.year', date('Y'));
    $timezone = config('app.timezone', 'UTC');

    return 'Dodoslav Novák | ' . escape_html($email) . ' | PHP & MySQL | ' . escape_html($year) . ' | Timezone ' . escape_html($timezone);
}

/**
 * Redirect with optional flash message
 *
 * @param string $url
 * @param string|null $message
 * @param string $type ('success', 'error', 'info')
 */
function redirect($url, $message = null, $type = 'info') {
    if ($message !== null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    header("Location: {$url}");
    exit;
}

/**
 * Get and clear flash message
 *
 * @return array|null ['message' => string, 'type' => string]
 */
function get_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];

        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        return $flash;
    }

    return null;
}

/**
 * Get old form input after validation error
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function old_input($key, $default = '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['old_input'][$key] ?? $default;
}

/**
 * Store form input for repopulation after error
 *
 * @param array $data
 */
function store_old_input($data) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['old_input'] = $data;
}

/**
 * Clear old input from session
 */
function clear_old_input() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    unset($_SESSION['old_input']);
}
