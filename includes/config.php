<?php
/**
 * Main Configuration File
 * 
 * This file contains all the configuration settings for the Fitness Club website
 * including database credentials, site settings, security configurations,
 * and various other important constants.
 * 
 * @package FitnessClub
 * @version 1.0
 */

/**
 * Database Configuration
 * These settings should be changed in production environment
 */
define('DB_HOST', 'localhost');          // Database host
define('DB_NAME', 'fitness_club');       // Database name
define('DB_USER', 'root');               // Database username (change in production)
define('DB_PASS', '');                   // Database password (change in production)
define('DB_CHARSET', 'utf8mb4');         // Database charset for proper encoding

/**
 * Site Configuration
 * Basic website settings and URLs
 */
define('SITE_NAME', 'Fitness Club');     // Website name
define('SITE_URL', 'http://localhost/fitness-club');  // Base URL (change in production)
define('ADMIN_EMAIL', 'admin@fitnessclub.com');       // Admin email for notifications

/**
 * Session Configuration
 * Security settings for PHP sessions
 */
ini_set('session.cookie_httponly', 1);   // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1);  // Force sessions to only use cookies
session_start();                         // Start the PHP session

/**
 * Error Reporting
 * Should be disabled in production environment
 */
error_reporting(E_ALL);                  // Report all PHP errors
ini_set('display_errors', 1);            // Display errors (disable in production)

/**
 * Time Zone Setting
 * Default timezone for date/time functions
 */
date_default_timezone_set('UTC');        // Set default timezone

/**
 * File Upload Configuration
 * Settings for handling file uploads
 */
define('MAX_FILE_SIZE', 5242880);        // Maximum file size (5MB in bytes)
define('ALLOWED_FILE_TYPES', [           // Allowed file extensions
    'jpg',
    'jpeg',
    'png',
    'gif'
]);
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/fitness-club/uploads/');

/**
 * Security Configuration
 * Critical security settings - must be changed in production
 */
define('CSRF_TOKEN_SECRET', 'your-secret-key-here');  // CSRF protection key
define('PASSWORD_PEPPER', 'your-pepper-key-here');    // Additional password security

/**
 * Payment Gateway Configuration
 * API keys for payment processing (if needed)
 */
define('STRIPE_PUBLIC_KEY', 'your-stripe-public-key');
define('STRIPE_SECRET_KEY', 'your-stripe-secret-key');

/**
 * Email Configuration
 * SMTP settings for sending emails
 */
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@example.com');
define('SMTP_PASS', 'your-email-password');

/**
 * Path Definitions
 * Important directory paths used throughout the application
 */
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('TEMPLATES_PATH', ROOT_PATH . 'templates/');

/**
 * Membership Types
 * Available membership plans in the system
 */
define('MEMBERSHIP_TYPES', [
    'basic' => 'Basic Membership',
    'standard' => 'Standard Membership',
    'premium' => 'Premium Membership'
]);

/**
 * Class Types
 * Available fitness class types
 */
define('CLASS_TYPES', [
    'yoga' => 'Yoga Classes',
    'cardio' => 'Cardio Training',
    'strength' => 'Strength Training',
    'zumba' => 'Zumba Classes',
    'pilates' => 'Pilates'
]);

/**
 * Generate CSRF Token
 * Creates a new CSRF token or returns existing one
 * 
 * @return string The CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 * Validates the provided CSRF token against stored token
 * 
 * @param string $token The token to verify
 * @return bool True if token is valid
 * @throws Exception If token validation fails
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        throw new Exception('CSRF token validation failed');
    }
    return true;
}

/**
 * Verify Password Function
 * 
 * This function verifies if the provided plain text password matches the hashed password.
 * 
 * @param string $plainPassword The plain text password to verify
 * @param string $hashedPassword The hashed password to compare against
 * @return bool Returns true if the passwords match, false otherwise
 */
function verifyPassword($plainPassword, $hashedPassword) {
    return password_verify($plainPassword, $hashedPassword);
}

function changePassword($userId, $newPassword) {
    // Include the configuration file to access database credentials
    require_once '../includes/config.php';

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Create a new PDO instance
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare the SQL statement
        $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        // Execute the statement
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        // Handle any errors
        echo 'Error: ' . $e->getMessage();
        return false;
    }
}
?>