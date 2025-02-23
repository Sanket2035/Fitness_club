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

// Handle class deletion
if (isset($_POST['delete_class'])) {
    $class_id = $_POST['class_id'];
    
    // Start transaction
    $db->beginTransaction();
    try {
        // Delete related schedules first
        $delete_schedules = "DELETE FROM schedules WHERE class_id = ?";
        $db->execute($delete_schedules, [$class_id]);
        
        // Then delete the class
        $delete_class = "DELETE FROM classes WHERE id = ?";
        $db->execute($delete_class, [$class_id]);
        
        $db->commit();
        $_SESSION['success_message'] = "Class deleted successfully!";
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Failed to delete class. Please try again.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all classes with trainer information
$classes_query = "SELECT c.*, t.name as trainer_name,
                        (SELECT COUNT(*) FROM schedules WHERE class_id = c.id) as schedule_count
                 FROM classes c
                 LEFT JOIN trainers t ON c.trainer_id = t.id
                 ORDER BY c.name ASC";
$classes = $db->fetchAll($classes_query);
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Classes Management Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Class Management</h2>
                <a href="admin/add_class.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Class
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
                                    <th>Image</th>
                                    <th>Class Name</th>
                                    <th>Description</th>
                                    <th>Trainer</th>
                                    <th>Capacity</th>
                                    <th>Duration</th>
                                    <th>Schedules</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><?php echo $class['id']; ?></td>
                                        <td>
                                            <?php if ($class['image']): ?>
                                                <img src="../uploads/classes/<?php echo htmlspecialchars($class['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($class['name']); ?>"
                                                     class="img-thumbnail" style="width: 50px;">
                                            <?php else: ?>
                                                <img src="../images/default-class.jpg" 
                                                     alt="Default" class="img-thumbnail" style="width: 50px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($class['name']); ?></td>
                                        <td>
                                            <?php 
                                            $description = htmlspecialchars($class['description']);
                                            echo strlen($description) > 100 ? 
                                                 substr($description, 0, 100) . '...' : 
                                                 $description;
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($class['trainer_name'] ?? 'No Trainer Assigned'); ?></td>
                                        <td><?php echo $class['capacity']; ?> people</td>
                                        <td><?php echo $class['duration']; ?> minutes</td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $class['schedule_count']; ?> schedules
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="admin/edit_class.php?id=<?php echo $class['id']; ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit Class">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a ="admin/class_schedules.php?id=<?php echo $class['id']; ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Manage Schedules">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </a>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                                                      method="post" style="display: inline;">
                                                    <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                                    <button type="submit" 
                                                            name="delete_class" 
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirmDelete('Are you sure you want to delete this class? This will also delete all related schedules.')"
                                                            data-bs-toggle="tooltip" 
                                                            title="Delete Class">
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