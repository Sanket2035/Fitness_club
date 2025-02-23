<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Initialize the session
// session_start();

// $db = new Database();
$db = Database::getInstance();

// Initialize variables
$name = $email = $subject = $message = "";
$name_err = $email_err = $subject_err = $message_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate subject
    if (empty(trim($_POST["subject"]))) {
        $subject_err = "Please enter a subject.";
    } else {
        $subject = trim($_POST["subject"]);
    }

    // Validate message
    if (empty(trim($_POST["message"]))) {
        $message_err = "Please enter your message.";
    } else {
        $message = trim($_POST["message"]);
    }

    // Check for errors before inserting into database
    if (empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_err)) {
        // Insert contact message into database
        $sql = "INSERT INTO contact_messages (name, email, subject, message, status, created_at) 
                VALUES (?, ?, ?, ?, 'new', NOW())";

        if ($db->execute($sql, [$name, $email, $subject, $message])) {
            $_SESSION['success_message'] = "Thank you for your message. We'll get back to you soon!";
            // Clear form fields after successful submission
            $name = $email = $subject = $message = "";
        } else {
            $_SESSION['error_message'] = "Something went wrong. Please try again later.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Contact Section -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4">Contact Us</h1>
            <p class="lead">Get in touch with us for any inquiries</p>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Contact Information -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h4 class="mb-4">Get In Touch</h4>
                        <div class="mb-4">
                            <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Location</h6>
                            <p>123 Fitness Street, Gym City, GC 12345</p>
                        </div>
                        <div class="mb-4">
                            <h6><i class="fas fa-envelope text-primary me-2"></i>Email</h6>
                            <p>info@fitnessclub.com</p>
                        </div>
                        <div class="mb-4">
                            <h6><i class="fas fa-phone text-primary me-2"></i>Phone</h6>
                            <p>+1 234 567 8900</p>
                        </div>
                        <div>
                            <h6><i class="fas fa-clock text-primary me-2"></i>Working Hours</h6>
                            <p>Monday - Friday: 6:00 AM - 10:00 PM<br>
                                Saturday - Sunday: 8:00 AM - 8:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name">Your Name</label>
                                    <input type="text" name="name"
                                        class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>"
                                        value="<?php echo $name; ?>" required>
                                    <span class="invalid-feedback"><?php echo $name_err; ?></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email">Your Email</label>
                                    <input type="email" name="email"
                                        class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                        value="<?php echo $email; ?>" required>
                                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="subject">Subject</label>
                                <input type="text" name="subject"
                                    class="form-control <?php echo (!empty($subject_err)) ? 'is-invalid' : ''; ?>"
                                    value="<?php echo $subject; ?>" required>
                                <span class="invalid-feedback"><?php echo $subject_err; ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="message">Message</label>
                                <textarea name="message" rows="5"
                                    class="form-control <?php echo (!empty($message_err)) ? 'is-invalid' : ''; ?>"
                                    required><?php echo $message; ?></textarea>
                                <span class="invalid-feedback"><?php echo $message_err; ?></span>
                            </div>

                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Map -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1884.7582246840775!2d74.18979813508236!3d19.128857842857947!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1738481020444!5m2!1sen!2sin"
                            width="100%"
                            height="450"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>