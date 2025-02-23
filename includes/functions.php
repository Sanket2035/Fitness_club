<?php
require_once 'config.php';
require_once 'db.php';

/**
 * Common utility functions for the Fitness Club website
 */

/**
 * Sanitize user input
 * @param string $input Input string to sanitize
 * @return string Sanitized string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date to readable format
 * @param string $date Date string
 * @param string $format Desired format (default: 'Y-m-d H:i:s')
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Generate slug from string
 * @param string $string Input string
 * @return string URL-friendly slug
 */
function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

/**
 * Upload file with validation
 * @param array $file $_FILES array element
 * @param string $destination Destination directory
 * @return string|bool Filename on success, false on failure
 */
function uploadFile($file, $destination) {
    try {
        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds limit');
        }

        // Validate file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ALLOWED_FILE_TYPES)) {
            throw new Exception('Invalid file type');
        }

        // Generate unique filename
        $filename = uniqid() . '.' . $fileExtension;
        $filepath = $destination . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }

        return $filename;
    } catch (Exception $e) {
        error_log("File Upload Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's membership status
 * @param int $userId User ID
 * @return array|bool Membership details or false
 */
function getUserMembership($userId) {
    try {
        $db = Database::getInstance();
        return $db->fetchOne(
            "SELECT m.*, p.name as plan_name, p.features 
             FROM user_memberships m 
             JOIN membership_plans p ON m.plan_id = p.id 
             WHERE m.user_id = ? AND m.status = 'active'",
            [$userId]
        );
    } catch (Exception $e) {
        error_log("Get Membership Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user can book a class
 * @param int $userId User ID
 * @param int $classId Class ID
 * @return bool
 */
function canBookClass($userId, $classId) {
    try {
        $membership = getUserMembership($userId);
        if (!$membership) {
            return false;
        }

        $db = Database::getInstance();
        $activeBookings = $db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM bookings 
             WHERE user_id = ? AND status = 'booked'",
            [$userId]
        );

        // Check booking limits based on membership type
        switch ($membership['plan_name']) {
            case 'Basic':
                return $activeBookings['count'] < 2;
            case 'Standard':
                return $activeBookings['count'] < 5;
            case 'Premium':
                return true;
            default:
                return false;
        }
    } catch (Exception $e) {
        error_log("Check Booking Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email notification
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @return bool Success status
 */
function sendEmail($to, $subject, $message) {
    try {
        $headers = [
            'From' => ADMIN_EMAIL,
            'Reply-To' => ADMIN_EMAIL,
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/html; charset=UTF-8'
        ];

        return mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get available time slots for a class
 * @param int $classId Class ID
 * @param string $date Date to check
 * @return array Available time slots
 */
function getAvailableTimeSlots($classId, $date) {
    try {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT s.*, 
                    (SELECT COUNT(*) FROM bookings b WHERE b.schedule_id = s.id) as booked,
                    c.capacity 
             FROM schedules s 
             JOIN classes c ON s.class_id = c.id 
             WHERE s.class_id = ? AND DATE(s.start_time) = ?
             HAVING booked < capacity",
            [$classId, $date]
        );
    } catch (Exception $e) {
        error_log("Time Slots Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Log system activity
 * @param string $action Action performed
 * @param string $details Action details
 * @param int $userId User ID (optional)
 */
function logActivity($action, $details, $userId = null) {
    try {
        $db = Database::getInstance();
        $db->insert('activity_logs', [
            'action' => $action,
            'details' => $details,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}
?>