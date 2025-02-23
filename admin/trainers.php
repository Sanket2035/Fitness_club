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

// $db = new Database();
$db = Database::getInstance();

// Handle trainer status toggle
if (isset($_POST['toggle_status'])) {
    $trainer_id = $_POST['trainer_id'];
    $new_status = $_POST['new_status'];

    if ($db->execute("UPDATE trainers SET status = ? WHERE id = ?", [$new_status, $trainer_id])) {
        $_SESSION['success_message'] = "Trainer status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update trainer status.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle trainer deletion
if (isset($_POST['delete_trainer'])) {
    $trainer_id = $_POST['trainer_id'];

    // Check if trainer has assigned classes
    $assigned_classes = $db->fetchOne(
        "SELECT COUNT(*) as count FROM classes WHERE trainer_id = ?",
        [$trainer_id]
    )['count'];

    if ($assigned_classes > 0) {
        $_SESSION['error_message'] = "Cannot delete trainer with assigned classes.";
    } else {
        if ($db->execute("DELETE FROM trainers WHERE id = ?", [$trainer_id])) {
            $_SESSION['success_message'] = "Trainer deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete trainer.";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all trainers with their class counts
$trainers_query = "SELECT t.*, 
                    (SELECT COUNT(*) FROM classes WHERE trainer_id = t.id) as class_count
                   FROM trainers t
                   ORDER BY t.name ASC";
$trainers = $db->fetchAll($trainers_query);
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Trainers Management Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Trainer Management</h2>
                <a href="admin/add_trainer.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Trainer
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
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                    <th>Contact</th>
                                    <th>Classes</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trainers as $trainer): ?>
                                    <tr>
                                        <td><?php echo $trainer['id']; ?></td>
                                        <td>
                                            <?php if ($trainer['photo']): ?>
                                                <img src="../uploads/trainers/<?php echo htmlspecialchars($trainer['photo']); ?>"
                                                    alt="<?php echo htmlspecialchars($trainer['name']); ?>"
                                                    class="img-thumbnail" style="width: 50px;">
                                            <?php else: ?>
                                                <img src="../images/default-trainer.jpg"
                                                    alt="Default" class="img-thumbnail" style="width: 50px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($trainer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($trainer['specialization']); ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($trainer['email']); ?></div>
                                            <small><?php echo htmlspecialchars($trainer['phone']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $trainer['class_count']; ?> classes
                                            </span>
                                        </td>
                                        <td>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                                method="post" style="display: inline;">
                                                <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                                                <input type="hidden" name="new_status"
                                                    value="<?php echo $trainer['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status"
                                                    class="btn btn-sm <?php echo $trainer['status'] === 'active' ? 'btn-success' : 'btn-warning'; ?>">
                                                    <?php echo ucfirst($trainer['status']); ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="admin/edit_trainer.php?id=<?php echo $trainer['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="tooltip"
                                                    title="Edit Trainer">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="admin/trainer_classes.php?id=<?php echo $trainer['id']; ?>"
                                                    class="btn btn-sm btn-info"
                                                    data-bs-toggle="tooltip"
                                                    title="View Classes">
                                                    <i class="fas fa-dumbbell"></i>
                                                </a>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                                    method="post" style="display: inline;">
                                                    <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                                                    <button type="submit"
                                                        name="delete_trainer"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirmDelete('Are you sure you want to delete this trainer?')"
                                                        <?php echo $trainer['class_count'] > 0 ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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