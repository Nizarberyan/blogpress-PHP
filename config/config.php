<?php
// config/config.php

// Error reporting (adjust based on environment)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blogpress');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Constants
define('APP_NAME', 'BlogPress');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/blogpress-PHP/');

// Security Settings
define('SALT', '4' . time()); // Change this to a unique value
define('TOKEN_EXPIRY', 3600); // 1 hour token expiration

// Path Constants
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Environment Configuration
define('APP_ENV', 'development'); // Can be 'development', 'staging', or 'production'

// Logging Configuration
define('LOG_PATH', ROOT_PATH . '/logs');

// Email Configuration
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_USER', 'your_email@example.com');
define('SMTP_PASS', 'your_email_password');
define('SMTP_PORT', 587);

// Security Configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 15 * 60); // 15 minutes

// Pagination Settings
define('ARTICLES_PER_PAGE', 10);
define('COMMENTS_PER_PAGE', 5);

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Function to safely get environment configuration
function getEnvConfig($key, $default = null)
{
    static $config = null;

    if ($config === null) {
        $config = [
            'debug' => APP_ENV === 'development',
            // Add more environment-specific configurations here
        ];
    }

    return $config[$key] ?? $default;
}

// Error handler
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    // Log errors
    $errorMessage = "Error [$errno] $errstr\n";
    $errorMessage .= "File: $errfile, Line: $errline\n";

    if (getEnvConfig('debug')) {
        echo "<pre>$errorMessage</pre>";
    }

    // Log to file
    error_log($errorMessage, 3, LOG_PATH . '/error.log');

    // Don't execute PHP internal error handler
    return true;
}

// Set custom error handler
set_error_handler('customErrorHandler');

// Database Connection Function
function getDatabaseConnection()
{
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Log the error
        error_log("Database Connection Error: " . $e->getMessage());

        // Show user-friendly error in production
        if (APP_ENV === 'production') {
            die("Sorry, we're experiencing technical difficulties.");
        } else {
            die("Database Connection Error: " . $e->getMessage());
        }
    }
}
