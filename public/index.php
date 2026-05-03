<?php

/**
 * Front Controller
 *
 * All requests are routed through this file
 */

require __DIR__ . '/../vendor/autoload.php';

use ScreepsOnline\App;
use ScreepsOnline\Middleware\CsrfMiddleware;

// Initialize application
$app = App::getInstance();
$router = $app->getRouter();

// Register global middleware
$router->addGlobalMiddleware(CsrfMiddleware::class);

// Define routes
require __DIR__ . '/../routes.php';

// Run application
$app->run();
