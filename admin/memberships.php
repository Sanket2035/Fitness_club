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

// Handle membership plan deletion
if (isset($_POST['delete_plan'])) {
    $plan_id = $_POST['plan_id'];
    
    // Check if plan has active members
    $active_members = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_memberships WHERE plan_id = ? AND status = 'active'",
        [$plan_id]
    )['count'];
    
    if ($active_members > 0) {
        $_SESSION['error_message'] = "Cannot delete plan with active members.";
    } else {
        if ($db->execute("DELETE FROM membership_plans WHERE id = ?", [$plan_id])) {
            $_SESSION['success_message'] = "Membership plan deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete membership plan.";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle plan status toggle
if (isset($_POST['toggle_status'])) {
    $plan_id = $_POST['plan_id'];
    $new_status = $_POST['new_status'];
    
    if ($db->execute("UPDATE membership_plans SET status = ? WHERE id = ?", [$new_status, $plan_id])) {
        $_SESSION['success_message'] = "Plan status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update plan status.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all membership plans with member counts
$plans_query = "SELECT mp.*, 
                (SELECT COUNT(*) FROM user_memberships um 
                 WHERE um.plan_id = mp.id AND um.status = 'active') as active_members
                FROM membership_plans mp
                ORDER BY mp.price ASC";
$plans = $db->fetchAll($plans_query);
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Membership Plans Management Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Membership Plans Management</h2>
                <a href="admin/add_plan.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Plan
                </a>
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
                <?php foreach ($plans as $plan): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($plan['name']); ?></h5>
                            </div>
                            <div class="card-body">
                                <h2 class="text-center mb-4">
                                    $<?php echo number_format($plan['price'], 2); ?>
                                    <small class="text-muted">/ month</small>
                                </h2>
                                
                                <div class="mb-3">
                                    <?php 
                                    $features = json_decode($plan['features'], true);
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
                                
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Duration:</strong> <?php echo $plan['duration']; ?> months</p>
                                    <p class="mb-1">
                                        <strong>Active Members:</strong> 
                                        <span class="badge bg-info"><?php echo $plan['active_members']; ?></span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-<?php echo $plan['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($plan['status']); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <div class="btn-group">
                                        <a href="admin/edit_plan.php?id=<?php echo $plan['id']; ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                                              method="post" style="display: inline;">
                                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                            <input type="hidden" name="new_status" 
                                                   value="<?php echo $plan['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                            <button type="submit" name="toggle_status" 
                                                    class="btn btn-<?php echo $plan['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                <i class="fas fa-power-off"></i> 
                                                <?php echo $plan['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                                          method="post">
                                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                        <button type="submit" name="delete_plan" 
                                                class="btn btn-danger"
                                                onclick="return confirmDelete('Are you sure you want to delete this plan?')"
                                                <?php echo $plan['active_members'] > 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>