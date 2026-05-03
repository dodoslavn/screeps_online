<?php

namespace ScreepsOnline\Services;

/**
 * CSRF Protection Service
 *
 * Generates and validates CSRF tokens
 */
class CsrfService
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_TIME_NAME = 'csrf_token_time';

    /**
     * Generate a new CSRF token
     *
     * @return string
     */
    public function generateToken(): string
    {
        // Generate new token if it doesn't exist or is expired
        if (!isset($_SESSION[self::TOKEN_NAME]) || $this->isTokenExpired()) {
            $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(32));
            $_SESSION[self::TOKEN_TIME_NAME] = time();
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Validate CSRF token
     *
     * @param string|null $token Token to validate
     * @return bool
     */
    public function validateToken(?string $token): bool
    {
        if (!isset($_SESSION[self::TOKEN_NAME]) || empty($token)) {
            return false;
        }

        // Check if token is expired
        if ($this->isTokenExpired()) {
            return false;
        }

        // Constant-time comparison to prevent timing attacks
        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    /**
     * Check if token is expired
     *
     * @return bool
     */
    private function isTokenExpired(): bool
    {
        if (!isset($_SESSION[self::TOKEN_TIME_NAME])) {
            return true;
        }

        $config = require __DIR__ . '/../../config/security.php';
        $lifetime = $config['csrf']['token_lifetime'];

        return (time() - $_SESSION[self::TOKEN_TIME_NAME]) > $lifetime;
    }

    /**
     * Get token from request
     *
     * @return string|null
     */
    public function getTokenFromRequest(): ?string
    {
        // Check POST data first
        if (isset($_POST[self::TOKEN_NAME])) {
            return $_POST[self::TOKEN_NAME];
        }

        // Check headers (for AJAX requests)
        $headers = getallheaders();
        if (isset($headers['X-CSRF-TOKEN'])) {
            return $headers['X-CSRF-TOKEN'];
        }

        return null;
    }

    /**
     * Regenerate token (useful after sensitive operations)
     */
    public function regenerateToken(): void
    {
        unset($_SESSION[self::TOKEN_NAME]);
        unset($_SESSION[self::TOKEN_TIME_NAME]);
        $this->generateToken();
    }

    /**
     * Clear token
     */
    public function clearToken(): void
    {
        unset($_SESSION[self::TOKEN_NAME]);
        unset($_SESSION[self::TOKEN_TIME_NAME]);
    }
}
