<?php
/**
 * Authentication Class
 * 
 * Handles all user authentication, registration, and session management
 * with secure password hashing and validation.
 * 
 * @package FitnessClub
 * @version 1.0
 */

require_once 'config.php';
require_once 'db.php';

class Auth {
    /** @var Database Database instance */
    private $db;
    
    /** @var int Maximum login attempts before lockout */
    const MAX_LOGIN_ATTEMPTS = 5;
    
    /** @var int Lockout time in minutes */
    const LOCKOUT_TIME = 15;

    /**
     * Constructor
     * Initializes database connection and starts session if not already started
     */
    public function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if user is an admin
     * 
     * @return bool True if user is an admin
     */
    public function isAdmin() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        $user = $this->db->fetchOne(
            "SELECT role FROM users WHERE id = ? AND status = 'active'",
            [$_SESSION['user_id']]
        );
        return $user && $user['role'] === 'admin';
    }

    /**
     * Get user by ID
     * 
     * @param int $userId User ID to fetch
     * @return array|null User data or null if not found
     */
    public function getUserById($userId) {
        return $this->db->fetchOne(
            "SELECT id, name, email, phone, role, join_date, status 
             FROM users 
             WHERE id = ? AND status = 'active'",
            [$userId]
        );
    }

    /**
     * Get current logged in user's data
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return $this->getUserById($_SESSION['user_id']);
    }

    /**
     * Check if email already exists in database
     * 
     * @param string $email Email to check
     * @return bool True if email exists
     */
    public function emailExists($email) {
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        return $existing !== false;
    }

    /**
     * Register a new user
     * 
     * @param array $userData User registration data
     * @return array Associative array with 'success' and 'message' keys
     */
    public function register($userData) {
        try {
            // Validate email format
            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Check if email already exists
            if ($this->emailExists($userData['email'])) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            // Hash password with pepper
            $hashedPassword = password_hash(
                $userData['password'] . PASSWORD_PEPPER,
                PASSWORD_DEFAULT
            );

            // Prepare user data for insertion
            $insertData = [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $hashedPassword,
                'phone' => $userData['phone'] ?? null,
                'role' => $userData['role'] ?? 'member',
                'join_date' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ];

            // Begin transaction
            $this->db->beginTransaction();

            // Insert user
            $userId = $this->db->insert('users', $insertData);

            // Create default membership record if specified
            if (!empty($userData['membership_plan'])) {
                $membershipData = [
                    'user_id' => $userId,
                    'plan_id' => $userData['membership_plan'],
                    'start_date' => date('Y-m-d'),
                    'status' => 'active'
                ];
                $this->db->insert('user_memberships', $membershipData);
            }

            $this->db->commit();
            return ['success' => true, 'user_id' => $userId];

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * Login user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Login result with success status and message
     */
    public function login($email, $password) {
        try {
            $user = $this->db->fetchOne(
                "SELECT id, password, role, status FROM users WHERE email = ?",
                [$email]
            );

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Account is not active'];
            }

            if (password_verify($password . PASSWORD_PEPPER, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['loggedin'] = true;
                $_SESSION['role'] = $user['role'];
                return ['success' => true, 'user_id' => $user['id'], 'role' => $user['role']];
            }

            return ['success' => false, 'message' => 'Invalid email or password'];

        } catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    /**
     * Logout current user
     */
    public function logout() {
        $_SESSION = array();
        session_destroy();
    }

    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $userData Updated user data
     * @return array Update result with success status and message
     */
    public function updateProfile($userId, $userData) {
        try {
            $updateData = array_intersect_key($userData, array_flip(['name', 'phone', 'email']));
            
            if (!empty($userData['password'])) {
                $updateData['password'] = password_hash(
                    $userData['password'] . PASSWORD_PEPPER,
                    PASSWORD_DEFAULT
                );
            }

            $this->db->update('users', $updateData, ['id' => $userId]);
            return ['success' => true, 'message' => 'Profile updated successfully'];

        } catch (Exception $e) {
            error_log("Profile Update Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile update failed: ' . $e->getMessage()];
        }
    }
}
?>