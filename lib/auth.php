<?php
/**
 * Authentication Utilities
 *
 * Provides secure password hashing and authentication functions
 */

/**
 * Hash a password securely
 *
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a hash
 *
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is authenticated, redirect to login if not
 */
function require_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['account'])) {
        header('Location: /account/login');
        exit;
    }
}

/**
 * Check if user is guest, redirect to account if logged in
 */
function require_guest() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['account'])) {
        header('Location: /account');
        exit;
    }
}

/**
 * Regenerate session ID (call after login/logout)
 */
function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Get currently logged in username
 *
 * @return string|null
 */
function current_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['account'] ?? null;
}
