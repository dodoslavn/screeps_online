<?php

namespace ScreepsOnline\Middleware;

use ScreepsOnline\Services\AuthService;

/**
 * Guest Middleware
 *
 * Redirects authenticated users away from guest pages (login/register)
 */
class GuestMiddleware
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
        if ($this->auth->isAuthenticated()) {
            // Already logged in, redirect to account page
            redirect('/account');
            return false;
        }

        return true;
    }
}
