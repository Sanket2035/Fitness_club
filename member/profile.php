<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Initialize the session
// session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Get user information
$auth = new Auth();
$user = $auth->getUserById($_SESSION["user_id"]);

// Initialize error variables
$name_err = $email_err = $phone_err = $password_err = $confirm_password_err = "";
$success_message = $error_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check which form was submitted
    if (isset($_POST["update_profile"])) {
        // Validate name
        $name = trim($_POST["name"]);
        if (empty($name)) {
            $name_err = "Please enter your name.";
        }

        // Validate email
        $email = trim($_POST["email"]);
        if (empty($email)) {
            $email_err = "Please enter an email.";
        } elseif ($email !== $user['email'] && $auth->emailExists($email)) {
            $email_err = "This email is already taken.";
        }

        // Validate phone
        $phone = trim($_POST["phone"]);
        if (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {
            $phone_err = "Please enter a valid phone number.";
        }

        // If no errors, update profile
        if (empty($name_err) && empty($email_err) && empty($phone_err)) {
            $result = $auth->updateProfile($_SESSION["user_id"], [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ]);

            if ($result['success']) {
                $success_message = "Profile updated successfully!";
                $user = $auth->getUserById($_SESSION["user_id"]); // Refresh user data
            } else {
                $error_message = "Something went wrong. Please try again later.";
            }
        }
    } elseif (isset($_POST["change_password"])) {
        // Validate current password
        if (empty(trim($_POST["current_password"]))) {
            $password_err = "Please enter your current password.";
        } elseif (!$auth->verifyPassword($_SESSION["user_id"], $_POST["current_password"])) {
            $password_err = "Current password is incorrect.";
        }

        // Validate new password
        if (empty(trim($_POST["new_password"]))) {
            $password_err = "Please enter a new password.";
        } elseif (strlen(trim($_POST["new_password"])) < 8) {
            $password_err = "Password must have at least 8 characters.";
        }

        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm the password.";
        } elseif ($_POST["new_password"] !== $_POST["confirm_password"]) {
            $confirm_password_err = "Passwords did not match.";
        }

        // If no errors, change password
        if (empty($password_err) && empty($confirm_password_err)) {
            $result = $auth->changePassword($_SESSION["user_id"], $_POST["new_password"]);
            
            if ($result['success']) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Something went wrong. Please try again later.";
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<!-- Profile Section -->
<div class="container py-5">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h2>My Profile</h2>
            <hr>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>">
                            <span class="invalid-feedback"><?php echo $name_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                            <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="current_password">Current Password</label>
                            <input type="password" name="current_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="new_password">New Password</label>
                            <input type="password" name="new_password" class="form-control">
                            <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>