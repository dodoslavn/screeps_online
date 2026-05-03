<?php

namespace ScreepsOnline\Middleware;

use ScreepsOnline\Services\CsrfService;

/**
 * CSRF Middleware
 *
 * Validates CSRF tokens on POST requests
 */
class CsrfMiddleware
{
    private CsrfService $csrf;

    public function __construct()
    {
        $this->csrf = new CsrfService();
    }

    /**
     * Handle the middleware
     *
     * @return bool True to continue, false to stop
     */
    public function handle(): bool
    {
        // Only validate POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        // Get token from request
        $token = $this->csrf->getTokenFromRequest();

        // Validate token
        if (!$this->csrf->validateToken($token)) {
            logMessage('CSRF token validation failed', 'WARNING', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'path' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);

            http_response_code(403);
            echo "CSRF token validation failed. Please refresh the page and try again.";
            return false;
        }

        return true;
    }
}
