<?php

namespace ScreepsOnline\Middleware;

/**
 * Rate Limit Middleware
 *
 * Prevents abuse by limiting requests from same IP/user
 */
class RateLimitMiddleware
{
    private string $action;
    private int $maxAttempts;
    private int $window;

    /**
     * @param string $action Action identifier (e.g., 'login', 'register')
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $window Time window in seconds
     */
    public function __construct(string $action = 'default', int $maxAttempts = 5, int $window = 900)
    {
        $this->action = $action;
        $this->maxAttempts = $maxAttempts;
        $this->window = $window;
    }

    /**
     * Handle the middleware
     *
     * @return bool True to continue, false to stop
     */
    public function handle(): bool
    {
        $identifier = $this->getIdentifier();
        $key = "rate_limit_{$this->action}_{$identifier}";

        // Initialize or get existing attempts
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $data = $_SESSION[$key];

        // Check if window has expired
        if ((time() - $data['first_attempt']) > $this->window) {
            // Reset counter
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
            $data = $_SESSION[$key];
        }

        // Check if limit exceeded
        if ($data['attempts'] >= $this->maxAttempts) {
            $remainingTime = $this->window - (time() - $data['first_attempt']);
            $minutes = ceil($remainingTime / 60);

            logMessage("Rate limit exceeded for action '{$this->action}'", 'WARNING', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'identifier' => $identifier
            ]);

            http_response_code(429);
            echo "Too many attempts. Please try again in {$minutes} minute(s).";
            return false;
        }

        // Increment attempts
        $_SESSION[$key]['attempts']++;

        return true;
    }

    /**
     * Get unique identifier for rate limiting (IP + User Agent)
     *
     * @return string
     */
    private function getIdentifier(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        return md5($ip . $userAgent);
    }

    /**
     * Reset rate limit for current identifier
     */
    public function reset(): void
    {
        $identifier = $this->getIdentifier();
        $key = "rate_limit_{$this->action}_{$identifier}";
        unset($_SESSION[$key]);
    }
}
