<?php

/**
 * Simple Autoloader
 *
 * No Composer needed - pure PHP autoloading
 */

spl_autoload_register(function ($class) {
    // Only autoload ScreepsOnline namespace
    if (strpos($class, 'ScreepsOnline\\') === 0) {
        // Convert namespace to file path
        $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 14)) . '.php';

        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});

// Load helper functions
require __DIR__ . '/../src/helpers.php';
