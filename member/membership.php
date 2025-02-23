<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Initialize the session
// session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// $db = new Database();

$db = Database::getInstance();
$auth = new Auth();
$user = $auth->getUserById($_SESSION["user_id"]);

// Get user's current membership
$membership_query = "SELECT um.*, mp.name as plan_name, mp.description as plan_description, 
                           mp.price, mp.features
                    FROM user_memberships um 
                    JOIN membership_plans mp ON um.plan_id = mp.id 
                    WHERE um.user_id = ? AND um.status = 'active'";
$current_membership = $db->fetchOne($membership_query, [$_SESSION["user_id"]]);

// Get all available membership plans
$plans_query = "SELECT * FROM membership_plans ORDER BY price ASC";
$available_plans = $db->fetchAll($plans_query);


// Handle membership upgrade/renewal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upgrade_membership'])) {
    $plan_id = $_POST['plan_id'];
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+1 year')); // Default 1-year membership
    
    // Start transaction
    $db->beginTransaction();
    try {
        // Deactivate current membership if exists
        if ($current_membership) {
            $update_query = "UPDATE user_memberships SET status = 'expired' 
                           WHERE user_id = ? AND status = 'active'";
            $db->execute($update_query, [$_SESSION["user_id"]]);
        }
        
        // Insert new membership
        $insert_query = "INSERT INTO user_memberships (user_id, plan_id, start_date, end_date, status) 
                        VALUES (?, ?, ?, ?, 'active')";
        $db->execute($insert_query, [$_SESSION["user_id"], $plan_id, $start_date, $end_date]);
        
        $db->commit();
        $_SESSION['success_message'] = "Membership updated successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Failed to update membership. Please try again.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<!-- Membership Section -->
<div class="container py-5">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h2>My Membership</h2>
            <hr>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Current Membership Status -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Current Membership Status</h5>
                </div>
                <div class="card-body">
                    <?php if ($current_membership): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo htmlspecialchars($current_membership['plan_name']); ?></h4>
                                <p><?php echo htmlspecialchars($current_membership['plan_description']); ?></p>
                                <p><strong>Start Date:</strong> <?php echo date('F d, Y', strtotime($current_membership['start_date'])); ?></p>
                                <p><strong>End Date:</strong> <?php echo date('F d, Y', strtotime($current_membership['end_date'])); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-success">Active</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5>Membership Features:</h5>
                                <?php 
                                $features = json_decode($current_membership['features'], true);
                                if ($features): 
                                ?>
                                    <ul class="list-group">
                                        <?php foreach ($features as $feature): ?>
                                            <li class="list-group-item">
                                                <i class="fas fa-check text-success"></i> 
                                                <?php echo htmlspecialchars($feature); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            You don't have an active membership. Please select a plan below to get started.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Available Plans -->
        <div class="col-md-12">
            <h3 class="mb-4">Available Membership Plans</h3>
            <div class="row">
                <?php foreach ($available_plans as $plan): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header text-center">
                                <h4 class="my-0 font-weight-normal"><?php echo htmlspecialchars($plan['name']); ?></h4>
                            </div>
                            <div class="card-body">
                                <h1 class="card-title pricing-card-title text-center">
                                    $<?php echo number_format($plan['price'], 2); ?>
                                    <small class="text-muted">/ month</small>
                                </h1>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <?php 
                                    $features = json_decode($plan['features'], true);
                                    foreach ($features as $feature): 
                                    ?>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success"></i> 
                                            <?php echo htmlspecialchars($feature); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                    <button type="submit" name="upgrade_membership" 
                                            class="btn btn-lg btn-block btn-primary w-100"
                                            <?php echo ($current_membership && $current_membership['plan_id'] == $plan['id']) ? 'disabled' : ''; ?>>
                                        <?php echo ($current_membership && $current_membership['plan_id'] == $plan['id']) ? 'Current Plan' : 'Select Plan'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>