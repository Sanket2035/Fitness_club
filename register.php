<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Initialize the session
// session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: member/dashboard.php");
    exit;
}

// Initialize variables
$name = $email = $password = $confirm_password = $phone = "";
$name_err = $email_err = $password_err = $confirm_password_err = $phone_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $auth = new Auth();
        if ($auth->emailExists(trim($_POST["email"]))) {
            $email_err = "This email is already registered.";
        } else {
            $email = trim($_POST["email"]);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }

    // Validate phone (optional)
    if (!empty(trim($_POST["phone"]))) {
        if (!preg_match("/^[0-9]{10}$/", trim($_POST["phone"]))) {
            $phone_err = "Please enter a valid phone number.";
        } else {
            $phone = trim($_POST["phone"]);
        }
    }

    // Check input errors before inserting in database
    if (
        empty($name_err) && empty($email_err) && empty($password_err) &&
        empty($confirm_password_err) && empty($phone_err)
    ) {

        $auth = new Auth();
        $result = $auth->register([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone
        ]);

        if ($result['success']) {
            // Set success message and redirect to login page
            $_SESSION['success_message'] = "Registration successful! Please login.";
            header("location: login.php");
            exit;
        } else {
            $register_err = "Something went wrong. Please try again later.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Registration Section -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Create an Account</h2>

                    <?php
                    if (!empty($register_err)) {
                        echo '<div class="alert alert-danger">' . $register_err . '</div>';
                    }
                    ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name">Full Name</label>
                                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>"
                                    value="<?php echo $name; ?>" required>
                                <span class="invalid-feedback"><?php echo $name_err; ?></span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email">Email</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                    value="<?php echo $email; ?>" required>
                                <span class="invalid-feedback"><?php echo $email_err; ?></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password">Password</label>
                                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                                    required>
                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
                                    required>
                                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone">Phone Number (Optional)</label>
                            <input type="tel" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $phone; ?>">
                            <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="terms.php">Terms & Conditions</a> and <a href="privacy.php">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="form-group d-grid">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>

                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="login.php">Login here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>