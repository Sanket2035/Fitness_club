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

// Check if trainer ID is provided
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    $_SESSION['error_message'] = "No trainer specified.";
    header("location: trainers.php");
    exit;
}

$trainer_id = $_GET["id"];

// Get trainer details
$trainer = $db->fetchOne("SELECT * FROM trainers WHERE id = ?", [$trainer_id]);
if (!$trainer) {
    $_SESSION['error_message'] = "Trainer not found.";
    header("location: trainers.php");
    exit;
}

// Get all classes assigned to this trainer with schedule information
$classes_query = "SELECT c.*, 
                        (SELECT COUNT(*) FROM schedules WHERE class_id = c.id) as schedule_count,
                        (SELECT COUNT(*) FROM bookings b 
                         JOIN schedules s ON b.schedule_id = s.id 
                         WHERE s.class_id = c.id AND b.status = 'booked') as total_bookings
                 FROM classes c 
                 WHERE c.trainer_id = ?
                 ORDER BY c.name ASC";
$classes = $db->fetchAll($classes_query, [$trainer_id]);

// Get available classes (not assigned to any trainer)
$available_classes_query = "SELECT * FROM classes 
                          WHERE trainer_id IS NULL 
                          ORDER BY name ASC";
$available_classes = $db->fetchAll($available_classes_query);

// Handle class assignment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['assign_class'])) {
        $class_id = $_POST['class_id'];
        
        if ($db->execute("UPDATE classes SET trainer_id = ? WHERE id = ?", 
            [$trainer_id, $class_id])) {
            $_SESSION['success_message'] = "Class assigned successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to assign class.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $trainer_id);
        exit;
    }
    
    if (isset($_POST['unassign_class'])) {
        $class_id = $_POST['class_id'];
        
        if ($db->execute("UPDATE classes SET trainer_id = NULL WHERE id = ? AND trainer_id = ?", 
            [$class_id, $trainer_id])) {
            $_SESSION['success_message'] = "Class unassigned successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to unassign class.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $trainer_id);
        exit;
    }
}
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Trainer Classes Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Classes - <?php echo htmlspecialchars($trainer['name']); ?></h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignClassModal">
                    <i class="fas fa-plus"></i> Assign New Class
                </button>
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

            <!-- Trainer Info Card -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <?php if ($trainer['photo']): ?>
                                <img src="../uploads/trainers/<?php echo htmlspecialchars($trainer['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($trainer['name']); ?>"
                                     class="img-thumbnail">
                            <?php else: ?>
                                <img src="../images/default-trainer.jpg" 
                                     alt="Default" class="img-thumbnail">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-10">
                            <h4><?php echo htmlspecialchars($trainer['name']); ?></h4>
                            <p><strong>Specialization:</strong> <?php echo htmlspecialchars($trainer['specialization']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($trainer['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($trainer['phone']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Classes Table -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Classes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($classes)): ?>
                        <div class="alert alert-info">
                            No classes assigned to this trainer yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Description</th>
                                        <th>Duration</th>
                                        <th>Capacity</th>
                                        <th>Schedules</th>
                                        <th>Bookings</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['name']); ?></td>
                                            <td>
                                                <?php 
                                                $description = htmlspecialchars($class['description']);
                                                echo strlen($description) > 100 ? 
                                                     substr($description, 0, 100) . '...' : 
                                                     $description;
                                                ?>
                                            </td>
                                            <td><?php echo $class['duration']; ?> minutes</td>
                                            <td><?php echo $class['capacity']; ?> people</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $class['schedule_count']; ?> schedules
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo $class['total_bookings']; ?> bookings
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="admin/class_schedules.php?id=<?php echo $class['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-calendar-alt"></i> Schedules
                                                    </a>
                                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $trainer_id); ?>" 
                                                          method="post" style="display: inline;">
                                                        <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                                        <button type="submit" 
                                                                name="unassign_class" 
                                                                class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Are you sure you want to unassign this class?')">
                                                            <i class="fas fa-times"></i> Unassign
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Class Modal -->
<div class="modal fade" id="assignClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Class to Trainer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($available_classes)): ?>
                    <div class="alert alert-info">
                        No available classes to assign.
                    </div>
                <?php else: ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $trainer_id); ?>" method="post">
                        <div class="mb-3">
                            <label for="class_id">Select Class</label>
                            <select name="class_id" class="form-control" required>
                                <?php foreach ($available_classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="assign_class" class="btn btn-primary">Assign Class</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>