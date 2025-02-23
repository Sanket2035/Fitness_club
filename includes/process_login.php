<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

// Initialize Auth class
$auth = new Auth();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify CSRF token
if (!verify_csrf_token()) {
    $_SESSION['error_message'] = "Invalid request. Please try again.";
    header("Location: ../login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../login.php");
        exit();
    }

    try {
        // Attempt login using Auth class
        $result = $auth->login($email, $password);

        if ($result['success']) {
            // Handle remember me functionality if checked
            if ($remember) {
                $token = generate_remember_token();
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Store token in database
                $db = Database::getInstance();
                $db->insert('remember_tokens', [
                    'user_id' => $result['user_id'],
                    'token' => $token,
                    'expires' => $expires
                ]);
                
                // Set cookie
                setcookie(
                    'remember_token',
                    $token,
                    strtotime('+30 days'),
                    '/',
                    '',
                    true,
                    true
                );
            }

            // Log successful login
            logActivity('login', 'Successful login', $result['user_id']);

            // Redirect based on role
            if ($result['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../member/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
            header("Location: ../login.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred. Please try again later.";
        header("Location: ../login.php");
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header("Location: ../login.php");
    exit();
}

/**
 * Generate remember me token
 * 
 * @return string Secure random token
 */
function generate_remember_token() {
    return bin2hex(random_bytes(32));
}

/**
 * Verify CSRF token from form submission
 * 
 * @return bool True if token is valid
 */
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    $posted_token = $_POST['csrf_token'];
    $stored_token = $_SESSION['csrf_token'];
    
    // Clean up the stored token
    unset($_SESSION['csrf_token']);
    
    return hash_equals($stored_token, $posted_token);
}
?>