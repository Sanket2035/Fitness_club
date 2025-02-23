<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Initialize the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$db = new Database();

// Initialize variables
$name = $email = $phone = $specialization = $bio = "";
$name_err = $email_err = $phone_err = $specialization_err = $bio_err = $photo_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter trainer's name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        // Check if email already exists
        if ($db->fetchOne("SELECT id FROM trainers WHERE email = ?", [trim($_POST["email"])])) {
            $email_err = "This email is already registered.";
        } else {
            $email = trim($_POST["email"]);
        }
    }
    
    // Validate phone
    if (!empty(trim($_POST["phone"])) && !preg_match("/^[0-9]{10}$/", trim($_POST["phone"]))) {
        $phone_err = "Please enter a valid 10-digit phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }
    
    // Validate specialization
    if (empty(trim($_POST["specialization"]))) {
        $specialization_err = "Please enter specialization.";
    } else {
        $specialization = trim($_POST["specialization"]);
    }
    
    // Validate bio
    if (empty(trim($_POST["bio"]))) {
        $bio_err = "Please enter trainer's bio.";
    } else {
        $bio = trim($_POST["bio"]);
    }
    
    // Handle photo upload
    $photo_name = null;
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["photo"]["name"];
        $filetype = $_FILES["photo"]["type"];
        $filesize = $_FILES["photo"]["size"];
        
        // Verify file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, $allowed)) {
            $photo_err = "Please select a valid image format (JPG, JPEG, PNG).";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $photo_err = "Photo size must be less than 5MB.";
        }
        
        // Verify MIME type
        if (in_array($filetype, $allowed)) {
            // Generate unique filename
            $photo_name = uniqid() . "." . $ext;
            $uploadpath = "../uploads/trainers/" . $photo_name;
            
            // Create directory if it doesn't exist
            if (!file_exists("../uploads/trainers/")) {
                mkdir("../uploads/trainers/", 0777, true);
            }
            
            if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadpath)) {
                $photo_err = "Error uploading photo.";
            }
        }
    }
    
    // Check for errors before inserting into database
    if (empty($name_err) && empty($email_err) && empty($phone_err) && 
        empty($specialization_err) && empty($bio_err) && empty($photo_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO trainers (name, email, phone, specialization, bio, photo, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')";
        
        if ($db->execute($sql, [$name, $email, $phone, $specialization, $bio, $photo_name])) {
            $_SESSION['success_message'] = "Trainer added successfully!";
            header("location: trainers.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Something went wrong. Please try again.";
        }
    }
}
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Add Trainer Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h2 class="mb-0">Add New Trainer</h2>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                          method="post" 
                          enctype="multipart/form-data">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name">Full Name</label>
                                <input type="text" name="name" 
                                       class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $name; ?>" required>
                                <span class="invalid-feedback"><?php echo $name_err; ?></span>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email">Email</label>
                                <input type="email" name="email" 
                                       class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $email; ?>" required>
                                <span class="invalid-feedback"><?php echo $email_err; ?></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone">Phone Number</label>
                                <input type="tel" name="phone" 
                                       class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $phone; ?>">
                                <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="specialization">Specialization</label>
                                <input type="text" name="specialization" 
                                       class="form-control <?php echo (!empty($specialization_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $specialization; ?>" required>
                                <span class="invalid-feedback"><?php echo $specialization_err; ?></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bio">Bio</label>
                            <textarea name="bio" 
                                    class="form-control <?php echo (!empty($bio_err)) ? 'is-invalid' : ''; ?>" 
                                    rows="4" required><?php echo $bio; ?></textarea>
                            <span class="invalid-feedback"><?php echo $bio_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="photo">Profile Photo</label>
                            <input type="file" name="photo" 
                                   class="form-control <?php echo (!empty($photo_err)) ? 'is-invalid' : ''; ?>" 
                                   accept="image/*">
                            <small class="form-text text-muted">Maximum file size: 5MB. Allowed formats: JPG, JPEG, PNG</small>
                            <span class="invalid-feedback"><?php echo $photo_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Add Trainer</button>
                            <a href="admin/trainers.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>