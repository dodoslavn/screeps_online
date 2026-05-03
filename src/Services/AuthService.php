<?php

namespace ScreepsOnline\Services;

use ScreepsOnline\Models\User;

/**
 * Authentication Service
 *
 * Handles user authentication and password management
 */
class AuthService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Hash a password securely
     *
     * Uses PASSWORD_ARGON2ID if available, falls back to PASSWORD_BCRYPT
     *
     * @param string $password
     * @return string Password hash
     */
    public function hashPassword(string $password): string
    {
        // Prefer Argon2id if available (PHP 7.3+, requires libsodium)
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID);
        }

        // Fallback to bcrypt (always available in PHP 5.5+)
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing (algorithm upgrade)
     *
     * @param string $hash
     * @return bool
     */
    public function needsRehash(string $hash): bool
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        return password_needs_rehash($hash, $algo);
    }

    /**
     * Attempt to log in a user
     *
     * @param string $username
     * @param string $password
     * @return array|false User data on success, false on failure
     */
    public function login(string $username, string $password)
    {
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            // User doesn't exist - use constant time to prevent username enumeration
            password_hash($password, PASSWORD_BCRYPT);
            return false;
        }

        // Check if old password format (needs migration)
        if ($this->userModel->needsPasswordReset($user['password'])) {
            // Old crypt() format - for security, require password reset
            return false;
        }

        // Verify password
        if (!$this->verifyPassword($password, $user['password'])) {
            return false;
        }

        // Successful login - regenerate session ID
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['login_time'] = time();

        // If password needs rehashing (algorithm upgrade), do it now
        if ($this->needsRehash($user['password'])) {
            $newHash = $this->hashPassword($password);
            $this->userModel->updatePassword($user['id_user'], $newHash);
        }

        logMessage("User '{$username}' logged in", 'INFO', [
            'user_id' => $user['id_user'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        return $user;
    }

    /**
     * Log out the current user
     */
    public function logout(): void
    {
        $username = $_SESSION['username'] ?? 'unknown';

        // Clear all session data
        $_SESSION = [];

        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(
                session_name(),
                '',
                time() - 42000,
                '/',
                '',
                (bool)env('SESSION_SECURE', false),
                true
            );
        }

        // Destroy session
        session_destroy();

        logMessage("User '{$username}' logged out", 'INFO');
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    public function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current username
     *
     * @return string|null
     */
    public function getCurrentUsername(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    /**
     * Get current user data
     *
     * @return array|false
     */
    public function getCurrentUser()
    {
        $userId = $this->getCurrentUserId();

        if (!$userId) {
            return false;
        }

        return $this->userModel->findById($userId);
    }

    /**
     * Register a new user
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return string|false User ID on success, false on failure
     */
    public function register(string $username, string $email, string $password)
    {
        // Hash password
        $passwordHash = $this->hashPassword($password);

        try {
            $userId = $this->userModel->create($username, $email, $passwordHash);

            logMessage("New user registered: '{$username}'", 'INFO', [
                'user_id' => $userId,
                'email' => $email
            ]);

            return $userId;
        } catch (\Exception $e) {
            logMessage("User registration failed for '{$username}': " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}
