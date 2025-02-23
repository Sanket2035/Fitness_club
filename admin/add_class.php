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
$name = $description = $duration = $capacity = $trainer_id = "";
$name_err = $description_err = $duration_err = $capacity_err = $image_err = "";

// Get all trainers for dropdown
$trainers = $db->fetchAll("SELECT id, name FROM trainers WHERE status = 'active' ORDER BY name");

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter the class name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter the class description.";
    } else {
        $description = trim($_POST["description"]);
    }
    
    // Validate duration
    if (empty(trim($_POST["duration"]))) {
        $duration_err = "Please enter the class duration.";
    } elseif (!is_numeric($_POST["duration"]) || $_POST["duration"] <= 0) {
        $duration_err = "Please enter a valid duration in minutes.";
    } else {
        $duration = trim($_POST["duration"]);
    }
    
    // Validate capacity
    if (empty(trim($_POST["capacity"]))) {
        $capacity_err = "Please enter the class capacity.";
    } elseif (!is_numeric($_POST["capacity"]) || $_POST["capacity"] <= 0) {
        $capacity_err = "Please enter a valid capacity.";
    } else {
        $capacity = trim($_POST["capacity"]);
    }
    
    // Handle image upload
    $image_name = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
        
        // Verify file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, $allowed)) {
            $image_err = "Please select a valid image format (JPG, JPEG, PNG).";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $image_err = "Image size must be less than 5MB.";
        }
        
        // Verify MYME type
        if (in_array($filetype, $allowed)) {
            // Generate unique filename
            $image_name = uniqid() . "." . $ext;
            $uploadpath = "../uploads/classes/" . $image_name;
            
            // Create directory if it doesn't exist
            if (!file_exists("../uploads/classes/")) {
                mkdir("../uploads/classes/", 0777, true);
            }
            
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $uploadpath)) {
                $image_err = "Error uploading image.";
            }
        }
    }
    
    // Check for errors before inserting into database
    if (empty($name_err) && empty($description_err) && empty($duration_err) && 
        empty($capacity_err) && empty($image_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO classes (name, description, duration, capacity, trainer_id, image) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $trainer_id = !empty($_POST["trainer_id"]) ? $_POST["trainer_id"] : null;
        
        if ($db->execute($sql, [$name, $description, $duration, $capacity, $trainer_id, $image_name])) {
            $_SESSION['success_message'] = "Class added successfully!";
            header("location: classes.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Something went wrong. Please try again.";
        }
    }
}
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Add Class Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h2 class="mb-0">Add New Class</h2>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                          method="post" 
                          enctype="multipart/form-data">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name">Class Name</label>
                                <input type="text" name="name" 
                                       class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $name; ?>" required>
                                <span class="invalid-feedback"><?php echo $name_err; ?></span>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="trainer_id">Trainer</label>
                                <select name="trainer_id" class="form-control">
                                    <option value="">Select Trainer (Optional)</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo $trainer['id']; ?>">
                                            <?php echo htmlspecialchars($trainer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" name="duration" 
                                       class="form-control <?php echo (!empty($duration_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $duration; ?>" required>
                                <span class="invalid-feedback"><?php echo $duration_err; ?></span>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="capacity">Capacity</label>
                                <input type="number" name="capacity" 
                                       class="form-control <?php echo (!empty($capacity_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $capacity; ?>" required>
                                <span class="invalid-feedback"><?php echo $capacity_err; ?></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description">Description</label>
                            <textarea name="description" 
                                    class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" 
                                    rows="4" required><?php echo $description; ?></textarea>
                            <span class="invalid-feedback"><?php echo $description_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="image">Class Image</label>
                            <input type="file" name="image" 
                                   class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>" 
                                   accept="image/*">
                            <small class="form-text text-muted">Maximum file size: 5MB. Allowed formats: JPG, JPEG, PNG</small>
                            <span class="invalid-feedback"><?php echo $image_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Add Class</button>
                            <a href="admin/classes.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>