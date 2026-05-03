<?php

namespace ScreepsOnline\Middleware;

use ScreepsOnline\Services\AuthService;

/**
 * Auth Middleware
 *
 * Requires user to be authenticated
 */
class AuthMiddleware
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    /**
     * Handle the middleware
     *
     * @return bool True to continue, false to stop
     */
    public function handle(): bool
    {
        if (!$this->auth->isAuthenticated()) {
            // Store intended URL for redirect after login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];

            // Redirect to login
            redirect('/account/login');
            return false;
        }

        return true;
    }
}
