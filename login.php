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
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        $auth = new Auth();
        $result = $auth->login($email, $password);

        if ($result['success']) {
            // Password is correct, start a new session
            session_start();

            // Store data in session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["user_id"] = $result['user_id'];
            $_SESSION["email"] = $email;

            // Redirect user to dashboard
            header("location: member/dashboard.php");
            exit;
        } else {
            $login_err = "Invalid email or password.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Login Section -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Login</h2>

                    <?php
                    if (!empty($login_err)) {
                        echo '<div class="alert alert-danger">' . $login_err . '</div>';
                    }
                    ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $email; ?>" required>
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>

                        <div class="form-group mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember">
                                <label class="custom-control-label" for="rememberMe">Remember me</label>
                            </div>
                        </div>

                        <div class="form-group d-grid">
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="forgot-password.php">Forgot Password?</a>
                        </div>

                        <hr>

                        <div class="text-center">
                            <p>Don't have an account? <a href="register.php">Sign up now</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>