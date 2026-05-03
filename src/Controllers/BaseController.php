<?php

namespace ScreepsOnline\Controllers;

use ScreepsOnline\Services\AuthService;
use ScreepsOnline\Services\ValidationService;
use ScreepsOnline\Services\CsrfService;

/**
 * Base Controller
 *
 * Provides common functionality for all controllers
 */
abstract class BaseController
{
    protected AuthService $auth;
    protected ValidationService $validator;
    protected CsrfService $csrf;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->validator = new ValidationService();
        $this->csrf = new CsrfService();
    }

    /**
     * Render a view
     *
     * @param string $view View file path relative to Views directory
     * @param array $data Data to pass to view
     */
    protected function render(string $view, array $data = []): void
    {
        // Make data available as variables
        extract($data);

        // Add CSRF token to all views
        $csrf_token = $this->csrf->generateToken();

        // Add auth data
        $current_user = $this->auth->getCurrentUser();
        $is_authenticated = $this->auth->isAuthenticated();

        // Add flash messages
        $success_message = getFlash('success');
        $error_message = getFlash('error');
        $errors = getFlash('errors', []);

        // Include view file
        $viewFile = __DIR__ . "/../Views/{$view}.php";

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$view}");
        }

        require $viewFile;
    }

    /**
     * Redirect to a URL
     *
     * @param string $url
     * @param int $statusCode
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        redirect($url, $statusCode);
    }

    /**
     * Redirect back to previous page or default
     *
     * @param string $default Default URL if no referrer
     */
    protected function redirectBack(string $default = '/'): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? $default;
        $this->redirect($referrer);
    }

    /**
     * Flash a success message and redirect
     *
     * @param string $message
     * @param string $url
     */
    protected function redirectWithSuccess(string $message, string $url): void
    {
        flash('success', $message);
        $this->redirect($url);
    }

    /**
     * Flash an error message and redirect
     *
     * @param string $message
     * @param string $url
     */
    protected function redirectWithError(string $message, string $url): void
    {
        flash('error', $message);
        $this->redirect($url);
    }

    /**
     * Flash validation errors and redirect back with old input
     *
     * @param array $errors
     * @param string|null $url
     */
    protected function redirectWithErrors(array $errors, ?string $url = null): void
    {
        flash('errors', $errors);
        $_SESSION['_old_input'] = $_POST;

        if ($url) {
            $this->redirect($url);
        } else {
            $this->redirectBack();
        }
    }

    /**
     * Get POST data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if request is POST
     *
     * @return bool
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Return JSON response
     *
     * @param array $data
     * @param int $statusCode
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Show 404 error
     */
    protected function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404');
    }

    /**
     * Show 403 forbidden error
     */
    protected function forbidden(): void
    {
        http_response_code(403);
        $this->render('errors/403');
    }
}
