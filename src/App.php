<?php

namespace ScreepsOnline;

/**
 * Application Bootstrap
 *
 * Initializes the application and handles configuration
 */
class App
{
    private Router $router;
    private static ?App $instance = null;

    private function __construct()
    {
        $this->loadEnvironment();
        $this->configureErrorHandling();
        $this->configureTimezone();
        $this->configureSession();
        $this->router = new Router();
    }

    /**
     * Get singleton instance
     *
     * @return App
     */
    public static function getInstance(): App
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Load environment variables
     */
    private function loadEnvironment(): void
    {
        // Configuration loaded via env() helper from config/env.php
        // No external dependencies needed
    }

    /**
     * Configure error handling
     */
    private function configureErrorHandling(): void
    {
        if (isDebug()) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            error_reporting(0);

            // Set custom error handler for production
            set_exception_handler(function ($exception) {
                logMessage(
                    'Uncaught exception: ' . $exception->getMessage(),
                    'ERROR',
                    [
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => $exception->getTraceAsString()
                    ]
                );

                http_response_code(500);
                echo "An error occurred. Please try again later.";
            });

            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                logMessage(
                    "Error [{$errno}]: {$errstr}",
                    'ERROR',
                    ['file' => $errfile, 'line' => $errline]
                );

                if (!(error_reporting() & $errno)) {
                    return false;
                }

                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            });
        }
    }

    /**
     * Configure timezone
     */
    private function configureTimezone(): void
    {
        $config = require __DIR__ . '/../config/app.php';
        date_default_timezone_set($config['timezone']);
    }

    /**
     * Configure secure session
     */
    private function configureSession(): void
    {
        $config = require __DIR__ . '/../config/security.php';
        $sessionConfig = $config['session'];

        // Don't start session if already started
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configure session cookie parameters
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'],
            'path' => '/',
            'domain' => '',
            'secure' => $sessionConfig['secure'],
            'httponly' => $sessionConfig['httponly'],
            'samesite' => $sessionConfig['samesite'],
        ]);

        // Set session name
        session_name($sessionConfig['name']);

        // Use strict session ID mode
        ini_set('session.use_strict_mode', '1');

        // Start session
        session_start();

        // Session timeout handling
        if (isset($_SESSION['LAST_ACTIVITY']) &&
            (time() - $_SESSION['LAST_ACTIVITY']) > $sessionConfig['lifetime']) {
            // Session expired
            session_unset();
            session_destroy();
            session_start();
        }

        $_SESSION['LAST_ACTIVITY'] = time();

        // Regenerate session ID periodically (every 30 minutes)
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } elseif (time() - $_SESSION['CREATED'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
    }

    /**
     * Get the router instance
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $this->router->dispatch();

        // Clean up flash messages after request
        $this->cleanupFlashMessages();
    }

    /**
     * Clean up one-time flash messages
     */
    private function cleanupFlashMessages(): void
    {
        if (isset($_SESSION['_flash'])) {
            unset($_SESSION['_flash']);
        }
        if (isset($_SESSION['_old_input'])) {
            unset($_SESSION['_old_input']);
        }
    }
}
