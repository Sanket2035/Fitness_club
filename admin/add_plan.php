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
$name = $description = $duration = $price = "";
$features = [];
$name_err = $description_err = $duration_err = $price_err = $features_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter the plan name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter the plan description.";
    } else {
        $description = trim($_POST["description"]);
    }
    
    // Validate duration
    if (empty(trim($_POST["duration"]))) {
        $duration_err = "Please enter the plan duration.";
    } elseif (!is_numeric($_POST["duration"]) || $_POST["duration"] <= 0) {
        $duration_err = "Please enter a valid duration in months.";
    } else {
        $duration = trim($_POST["duration"]);
    }
    
    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter the plan price.";
    } elseif (!is_numeric($_POST["price"]) || $_POST["price"] < 0) {
        $price_err = "Please enter a valid price.";
    } else {
        $price = trim($_POST["price"]);
    }
    
    // Validate features
    if (isset($_POST["features"]) && is_array($_POST["features"])) {
        $features = array_filter($_POST["features"], function($feature) {
            return !empty(trim($feature));
        });
    }
    if (empty($features)) {
        $features_err = "Please add at least one feature.";
    }
    
    // Check for errors before inserting into database
    if (empty($name_err) && empty($description_err) && empty($duration_err) && 
        empty($price_err) && empty($features_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO membership_plans (name, description, duration, price, features, status) 
                VALUES (?, ?, ?, ?, ?, 'active')";
        
        if ($db->execute($sql, [
            $name, 
            $description, 
            $duration, 
            $price, 
            json_encode($features)
        ])) {
            $_SESSION['success_message'] = "Membership plan added successfully!";
            header("location: memberships.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Something went wrong. Please try again.";
        }
    }
}
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Add Membership Plan Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h2 class="mb-0">Add New Membership Plan</h2>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="planForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name">Plan Name</label>
                                <input type="text" name="name" 
                                       class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $name; ?>" required>
                                <span class="invalid-feedback"><?php echo $name_err; ?></span>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="price">Monthly Price ($)</label>
                                <input type="number" name="price" step="0.01" 
                                       class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $price; ?>" required>
                                <span class="invalid-feedback"><?php echo $price_err; ?></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description">Description</label>
                                <textarea name="description" 
                                        class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" 
                                        rows="3" required><?php echo $description; ?></textarea>
                                <span class="invalid-feedback"><?php echo $description_err; ?></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duration">Duration (months)</label>
                                <input type="number" name="duration" 
                                       class="form-control <?php echo (!empty($duration_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $duration; ?>" required>
                                <span class="invalid-feedback"><?php echo $duration_err; ?></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Plan Features</label>
                            <div id="featuresContainer">
                                <div class="feature-input mb-2">
                                    <div class="input-group">
                                        <input type="text" name="features[]" class="form-control" 
                                               placeholder="Enter a feature" required>
                                        <button type="button" class="btn btn-danger remove-feature">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" id="addFeature">
                                <i class="fas fa-plus"></i> Add Feature
                            </button>
                            <?php if (!empty($features_err)): ?>
                                <div class="text-danger mt-2"><?php echo $features_err; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Create Plan</button>
                            <a href="member/memberships.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Management Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const featuresContainer = document.getElementById('featuresContainer');
    const addFeatureBtn = document.getElementById('addFeature');

    // Add new feature input
    addFeatureBtn.addEventListener('click', function() {
        const featureDiv = document.createElement('div');
        featureDiv.className = 'feature-input mb-2';
        featureDiv.innerHTML = `
            <div class="input-group">
                <input type="text" name="features[]" class="form-control" 
                       placeholder="Enter a feature" required>
                <button type="button" class="btn btn-danger remove-feature">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        featuresContainer.appendChild(featureDiv);
    });

    // Remove feature input
    featuresContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-feature') || 
            e.target.parentElement.classList.contains('remove-feature')) {
            const featureInput = e.target.closest('.feature-input');
            if (featuresContainer.children.length > 1) {
                featureInput.remove();
            }
        }
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>