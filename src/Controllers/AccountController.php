<?php

namespace ScreepsOnline\Controllers;

use ScreepsOnline\Models\User;
use ScreepsOnline\Middleware\RateLimitMiddleware;

/**
 * Account Controller
 *
 * Handles user authentication and account management
 */
class AccountController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login form
     */
    public function login(): void
    {
        $this->render('account/login', [
            'page_title' => 'Login'
        ]);
    }

    /**
     * Process login
     */
    public function authenticate(): void
    {
        // Apply rate limiting
        $rateLimiter = new RateLimitMiddleware('login', 5, 900); // 5 attempts per 15 minutes
        if (!$rateLimiter->handle()) {
            return;
        }

        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');

        // Validate input
        if (!$this->validator->required($username, 'username') ||
            !$this->validator->required($password, 'password')) {
            $this->redirectWithErrors($this->validator->getErrors(), '/account/login');
            return;
        }

        // Attempt login
        $user = $this->auth->login($username, $password);

        if (!$user) {
            // Check if user exists and has old password
            $existingUser = $this->userModel->findByUsername($username);
            if ($existingUser && $this->userModel->needsPasswordReset($existingUser['password'])) {
                flash('error', 'Your password needs to be reset for security reasons. Please use the password reset feature.');
                $this->redirect('/account/reset-password');
                return;
            }

            $this->redirectWithError('Invalid username or password', '/account/login');
            return;
        }

        // Reset rate limiter on successful login
        $rateLimiter->reset();

        // Redirect to intended URL or account page
        $intendedUrl = $_SESSION['intended_url'] ?? '/account';
        unset($_SESSION['intended_url']);

        $this->redirect($intendedUrl);
    }

    /**
     * Show registration form
     */
    public function register(): void
    {
        $this->render('account/register', [
            'page_title' => 'Create Account'
        ]);
    }

    /**
     * Process registration
     */
    public function store(): void
    {
        // Apply rate limiting
        $rateLimiter = new RateLimitMiddleware('register', 3, 3600); // 3 attempts per hour
        if (!$rateLimiter->handle()) {
            return;
        }

        $username = trim($this->post('username', ''));
        $email = trim($this->post('email', ''));
        $password = $this->post('password', '');
        $passwordConfirm = $this->post('password_confirm', '');

        // Validate username
        if (!$this->validator->validateUsername($username)) {
            $this->redirectWithErrors($this->validator->getErrors(), '/account/create');
            return;
        }

        // Validate email
        if (!$this->validator->validateEmail($email)) {
            $this->redirectWithErrors($this->validator->getErrors(), '/account/create');
            return;
        }

        // Validate password
        if (!$this->validator->validatePassword($password)) {
            $this->redirectWithErrors($this->validator->getErrors(), '/account/create');
            return;
        }

        // Check password confirmation
        if ($password !== $passwordConfirm) {
            $this->validator->addError('password_confirm', 'Passwords do not match');
            $this->redirectWithErrors($this->validator->getErrors(), '/account/create');
            return;
        }

        // Check if username exists
        if ($this->userModel->usernameExists($username)) {
            $this->validator->addError('username', 'Username already taken');
            $this->redirectWithErrors($this->validator->getErrors(), '/account/create');
            return;
        }

        // Check if email exists
        if ($this->userModel->emailExists($email)) {
            $this->validator->addError('email', 'Email already registered');
            $this->redirectWithErrors($this->validator->getErrors(), '/account/create');
            return;
        }

        // Register user
        $userId = $this->auth->register($username, $email, $password);

        if (!$userId) {
            $this->redirectWithError('Failed to create account. Please try again.', '/account/create');
            return;
        }

        // Auto-login the new user
        $this->auth->login($username, $password);

        $this->redirectWithSuccess('Account created successfully!', '/account');
    }

    /**
     * Show user profile
     */
    public function profile(): void
    {
        $user = $this->auth->getCurrentUser();
        $ownedServers = $this->userModel->getOwnedServers($user['id_user']);

        $this->render('account/profile', [
            'user' => $user,
            'owned_servers' => $ownedServers,
            'page_title' => 'My Account'
        ]);
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $this->auth->logout();
        $this->redirectWithSuccess('You have been logged out', '/');
    }

    /**
     * Show password reset request form
     */
    public function resetPasswordForm(): void
    {
        $this->render('account/reset-password', [
            'page_title' => 'Reset Password'
        ]);
    }

    /**
     * Send password reset email
     */
    public function sendResetEmail(): void
    {
        // Apply rate limiting
        $rateLimiter = new RateLimitMiddleware('password_reset', 3, 3600); // 3 attempts per hour
        if (!$rateLimiter->handle()) {
            return;
        }

        $email = trim($this->post('email', ''));

        // Validate email
        if (!$this->validator->validateEmail($email)) {
            $this->redirectWithErrors($this->validator->getErrors(), '/account/reset-password');
            return;
        }

        // Check if user exists
        $user = $this->userModel->findByEmail($email);

        // Always show success message to prevent email enumeration
        // In a real implementation, you would send an email here if user exists
        flash('success', 'If an account exists with this email, you will receive password reset instructions.');

        logMessage('Password reset requested for email: ' . $email, 'INFO', [
            'user_found' => $user ? 'yes' : 'no'
        ]);

        $this->redirect('/account/login');
    }

    /**
     * Show password reset confirmation form (with token)
     */
    public function resetPasswordConfirm(): void
    {
        $token = $this->get('token');

        if (!$token) {
            $this->redirectWithError('Invalid reset token', '/account/reset-password');
            return;
        }

        // In a real implementation, validate token from database
        $this->render('account/reset-password-confirm', [
            'token' => $token,
            'page_title' => 'Reset Password'
        ]);
    }

    /**
     * Process password reset
     */
    public function resetPassword(): void
    {
        $token = $this->post('token');
        $password = $this->post('password', '');
        $passwordConfirm = $this->post('password_confirm', '');

        if (!$token) {
            $this->redirectWithError('Invalid reset token', '/account/reset-password');
            return;
        }

        // Validate password
        if (!$this->validator->validatePassword($password)) {
            $this->redirectWithErrors($this->validator->getErrors(), "/account/reset-password/confirm?token={$token}");
            return;
        }

        // Check password confirmation
        if ($password !== $passwordConfirm) {
            $this->validator->addError('password_confirm', 'Passwords do not match');
            $this->redirectWithErrors($this->validator->getErrors(), "/account/reset-password/confirm?token={$token}");
            return;
        }

        // In a real implementation:
        // 1. Validate token from database
        // 2. Check if token is expired
        // 3. Get user ID from token
        // 4. Update password
        // 5. Delete token
        // 6. Send confirmation email

        $this->redirectWithSuccess('Password reset successfully! You can now login with your new password.', '/account/login');
    }
}
