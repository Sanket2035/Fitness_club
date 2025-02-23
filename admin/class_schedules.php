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

// Check if class ID is provided
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    $_SESSION['error_message'] = "No class specified.";
    header("location: classes.php");
    exit;
}

$class_id = $_GET["id"];

// Get class details
$class = $db->fetch("SELECT * FROM classes WHERE id = ?", [$class_id]);
if (!$class) {
    $_SESSION['error_message'] = "Class not found.";
    header("location: classes.php");
    exit;
}

// Handle schedule deletion
if (isset($_POST['delete_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    
    // Start transaction
    $db->beginTransaction();
    try {
        // Delete related bookings first
        $delete_bookings = "DELETE FROM bookings WHERE schedule_id = ?";
        $db->execute($delete_bookings, [$schedule_id]);
        
        // Then delete the schedule
        $delete_schedule = "DELETE FROM schedules WHERE id = ? AND class_id = ?";
        $db->execute($delete_schedule, [$schedule_id, $class_id]);
        
        $db->commit();
        $_SESSION['success_message'] = "Schedule deleted successfully!";
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Failed to delete schedule. Please try again.";
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $class_id);
    exit;
}

// Handle schedule addition/update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_schedule'])) {
    $schedule_id = !empty($_POST['schedule_id']) ? $_POST['schedule_id'] : null;
    $day = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];
    
    if ($schedule_id) {
        // Update existing schedule
        $sql = "UPDATE schedules SET day_of_week = ?, start_time = ?, end_time = ?, room = ? 
                WHERE id = ? AND class_id = ?";
        $params = [$day, $start_time, $end_time, $room, $schedule_id, $class_id];
    } else {
        // Add new schedule
        $sql = "INSERT INTO schedules (class_id, day_of_week, start_time, end_time, room) 
                VALUES (?, ?, ?, ?, ?)";
        $params = [$class_id, $day, $start_time, $end_time, $room];
    }
    
    if ($db->execute($sql, $params)) {
        $_SESSION['success_message'] = "Schedule " . ($schedule_id ? "updated" : "added") . " successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to " . ($schedule_id ? "update" : "add") . " schedule.";
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $class_id);
    exit;
}

// Get all schedules for this class
$schedules = $db->fetchAll(
    "SELECT s.*, 
            (SELECT COUNT(*) FROM bookings b WHERE b.schedule_id = s.id AND b.status = 'booked') as booked_count
     FROM schedules s 
     WHERE s.class_id = ? 
     ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
     s.start_time",
    [$class_id]
);
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Class Schedules Section -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Class Schedules - <?php echo htmlspecialchars($class['name']); ?></h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                    <i class="fas fa-plus"></i> Add Schedule
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

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Room</th>
                                    <th>Current Bookings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                        <td><?php echo date('g:i A', strtotime($schedule['start_time'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($schedule['end_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($schedule['room']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $schedule['booked_count']; ?> bookings
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#scheduleModal"
                                                        data-schedule='<?php echo json_encode($schedule); ?>'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $class_id); ?>" 
                                                      method="post" style="display: inline;">
                                                    <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                                    <button type="submit" 
                                                            name="delete_schedule" 
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirmDelete('Are you sure you want to delete this schedule? This will also delete all related bookings.')"
                                                            <?php echo $schedule['booked_count'] > 0 ? 'disabled' : ''; ?>>
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

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $class_id); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="schedule_id" id="schedule_id">
                    
                    <div class="mb-3">
                        <label for="day_of_week">Day of Week</label>
                        <select name="day_of_week" id="day_of_week" class="form-control" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="room">Room</label>
                        <input type="text" name="room" id="room" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_schedule" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Modal Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleModal = document.getElementById('scheduleModal');
    scheduleModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const scheduleData = button.getAttribute('data-schedule');
        
        const modalTitle = this.querySelector('.modal-title');
        const scheduleIdInput = this.querySelector('#schedule_id');
        const daySelect = this.querySelector('#day_of_week');
        const startTimeInput = this.querySelector('#start_time');
        const endTimeInput = this.querySelector('#end_time');
        const roomInput = this.querySelector('#room');
        
        if (scheduleData) {
            const schedule = JSON.parse(scheduleData);
            modalTitle.textContent = 'Edit Schedule';
            scheduleIdInput.value = schedule.id;
            daySelect.value = schedule.day_of_week;
            startTimeInput.value = schedule.start_time;
            endTimeInput.value = schedule.end_time;
            roomInput.value = schedule.room;
        } else {
            modalTitle.textContent = 'Add New Schedule';
            scheduleIdInput.value = '';
            daySelect.selectedIndex = 0;
            startTimeInput.value = '';
            endTimeInput.value = '';
            roomInput.value = '';
        }
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>