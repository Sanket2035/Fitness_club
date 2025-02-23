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

// Handle member status toggle
if (isset($_POST['toggle_status'])) {
    $member_id = $_POST['member_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE users SET status = ? WHERE id = ?";
    $result = $db->execute($update_query, [$new_status, $member_id]);
    
    if ($result) {
        $_SESSION['success_message'] = "Member status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update member status.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all members with their membership details
$members_query = "SELECT u.*, 
                        um.start_date as membership_start, 
                        um.end_date as membership_end,
                        mp.name as plan_name
                 FROM users u
                 LEFT JOIN user_memberships um ON u.id = um.user_id AND um.status = 'active'
                 LEFT JOIN membership_plans mp ON um.plan_id = mp.id
                 ORDER BY u.join_date DESC";
$members = $db->fetchAll($members_query);
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Members Management Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Member Management</h2>
                <a href="admin/add_member.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Member
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

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Membership Plan</th>
                                    <th>Membership Status</th>
                                    <th>Join Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?php echo $member['id']; ?></td>
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                        <td>
                                            <?php if ($member['plan_name']): ?>
                                                <?php echo htmlspecialchars($member['plan_name']); ?>
                                                <br>
                                                <small class="text-muted">
                                                    Valid until: <?php echo date('M d, Y', strtotime($member['membership_end'])); ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="badge bg-warning">No Active Plan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($member['membership_end']): ?>
                                                <?php if (strtotime($member['membership_end']) > time()): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($member['join_date'])); ?></td>
                                        <td>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                                                  method="post" style="display: inline;">
                                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                <input type="hidden" name="new_status" 
                                                       value="<?php echo $member['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" 
                                                        class="btn btn-sm <?php echo $member['status'] === 'active' ? 'btn-success' : 'btn-danger'; ?>">
                                                    <?php echo ucfirst($member['status']); ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="admin/edit_member.php?id=<?php echo $member['id']; ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit Member">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="view_member.php?id=<?php echo $member['id']; ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="member_bookings.php?id=<?php echo $member['id']; ?>" 
                                                   class="btn btn-sm btn-warning" 
                                                   data-bs-toggle="tooltip" 
                                                   title="View Bookings">
                                                    <i class="fas fa-calendar-check"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>