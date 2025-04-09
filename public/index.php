<?php
// /public/index.php
require_once __DIR__ . '/../vendor/autoload.php';

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the base directory
define('BASE_PATH', dirname(__DIR__));

// Autoload function
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = BASE_PATH . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Autoload Composer dependencies (if any)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Handle routes
require_once BASE_PATH . '/routes/api.php';
