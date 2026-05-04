<?php
/**
 * Security Utilities
 *
 * Provides CSRF protection, input validation, and output escaping
 */

/**
 * Escape HTML output to prevent XSS
 *
 * @param string $text
 * @return string
 */
function escape_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token and store in session
 *
 * @return string
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token matches session
 *
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate server address format (IP:PORT or domain:PORT)
 *
 * @param string $address
 * @return bool
 */
function validate_server_address($address) {
    if (empty($address)) {
        return false;
    }

    $parts = explode(':', $address);

    if (count($parts) !== 2) {
        return false;
    }

    list($host, $port) = $parts;

    // Validate port
    if (!ctype_digit($port) || $port < 1 || $port > 65535) {
        return false;
    }

    // Validate host (IP or domain)
    if (!filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_DOMAIN)) {
        return false;
    }

    return true;
}
