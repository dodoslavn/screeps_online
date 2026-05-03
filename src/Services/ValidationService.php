<?php

namespace ScreepsOnline\Services;

/**
 * Validation Service
 *
 * Handles all input validation
 */
class ValidationService
{
    private array $errors = [];

    /**
     * Validate email address
     *
     * @param string $email
     * @param string $field Field name for error messages
     * @return bool
     */
    public function validateEmail(string $email, string $field = 'email'): bool
    {
        if (empty($email)) {
            $this->errors[$field] = 'Email is required';
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Invalid email format';
            return false;
        }

        if (strlen($email) > 100) {
            $this->errors[$field] = 'Email must be less than 100 characters';
            return false;
        }

        return true;
    }

    /**
     * Validate username
     *
     * @param string $username
     * @param string $field Field name for error messages
     * @return bool
     */
    public function validateUsername(string $username, string $field = 'username'): bool
    {
        if (empty($username)) {
            $this->errors[$field] = 'Username is required';
            return false;
        }

        if (strlen($username) < 3) {
            $this->errors[$field] = 'Username must be at least 3 characters';
            return false;
        }

        if (strlen($username) > 20) {
            $this->errors[$field] = 'Username must be less than 20 characters';
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->errors[$field] = 'Username can only contain letters, numbers, and underscores';
            return false;
        }

        return true;
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @param string $field Field name for error messages
     * @return bool
     */
    public function validatePassword(string $password, string $field = 'password'): bool
    {
        $config = require __DIR__ . '/../../config/security.php';
        $requirements = $config['password'];

        if (empty($password)) {
            $this->errors[$field] = 'Password is required';
            return false;
        }

        if (strlen($password) < $requirements['min_length']) {
            $this->errors[$field] = "Password must be at least {$requirements['min_length']} characters";
            return false;
        }

        if ($requirements['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $this->errors[$field] = 'Password must contain at least one uppercase letter';
            return false;
        }

        if ($requirements['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $this->errors[$field] = 'Password must contain at least one lowercase letter';
            return false;
        }

        if ($requirements['require_number'] && !preg_match('/[0-9]/', $password)) {
            $this->errors[$field] = 'Password must contain at least one number';
            return false;
        }

        if ($requirements['require_special'] && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $this->errors[$field] = 'Password must contain at least one special character';
            return false;
        }

        return true;
    }

    /**
     * Validate server address (host:port format)
     *
     * @param string $address
     * @param string $field Field name for error messages
     * @return array|false Returns parsed address on success, false on failure
     */
    public function validateServerAddress(string $address, string $field = 'server')
    {
        if (empty($address)) {
            $this->errors[$field] = 'Server address is required';
            return false;
        }

        // Must be in format host:port
        if (strpos($address, ':') === false) {
            $this->errors[$field] = 'Server address must be in format host:port';
            return false;
        }

        $parts = explode(':', $address);
        if (count($parts) !== 2) {
            $this->errors[$field] = 'Server address must be in format host:port';
            return false;
        }

        [$host, $port] = $parts;

        // Validate port
        if (!ctype_digit($port)) {
            $this->errors[$field] = 'Port must be numeric';
            return false;
        }

        $portNum = (int)$port;
        if ($portNum < 1024 || $portNum > 65535) {
            $this->errors[$field] = 'Port must be between 1024 and 65535';
            return false;
        }

        // Validate host (IP address or domain)
        $validIp = filter_var($host, FILTER_VALIDATE_IP);
        $validDomain = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

        if (!$validIp && !$validDomain) {
            $this->errors[$field] = 'Invalid hostname or IP address';
            return false;
        }

        // Additional security: prevent private IPs in production
        if ($validIp && !env('APP_DEBUG', false)) {
            $privateRanges = [
                '/^127\./',        // 127.0.0.0/8
                '/^10\./',         // 10.0.0.0/8
                '/^172\.(1[6-9]|2[0-9]|3[01])\./',  // 172.16.0.0/12
                '/^192\.168\./',   // 192.168.0.0/16
            ];

            foreach ($privateRanges as $pattern) {
                if (preg_match($pattern, $host)) {
                    $this->errors[$field] = 'Private IP addresses are not allowed';
                    return false;
                }
            }
        }

        return [
            'host' => $host,
            'port' => $portNum,
            'address' => $address
        ];
    }

    /**
     * Validate required field
     *
     * @param mixed $value
     * @param string $field
     * @return bool
     */
    public function required($value, string $field): bool
    {
        if (empty($value) && $value !== '0') {
            $this->errors[$field] = ucfirst($field) . ' is required';
            return false;
        }

        return true;
    }

    /**
     * Validate string length
     *
     * @param string $value
     * @param int $min
     * @param int $max
     * @param string $field
     * @return bool
     */
    public function length(string $value, int $min, int $max, string $field): bool
    {
        $length = strlen($value);

        if ($length < $min) {
            $this->errors[$field] = ucfirst($field) . " must be at least {$min} characters";
            return false;
        }

        if ($length > $max) {
            $this->errors[$field] = ucfirst($field) . " must be less than {$max} characters";
            return false;
        }

        return true;
    }

    /**
     * Get all validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get a specific error
     *
     * @param string $field
     * @return string|null
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Check if there are any errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Clear all errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Add a custom error
     *
     * @param string $field
     * @param string $message
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }
}
