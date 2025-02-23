<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Initialize the session
// session_start();

// $db = new Database();
$db = Database::getInstance();

// Get all active classes with their schedules and trainers
$schedule_query = "SELECT s.*, c.name as class_name, c.description, c.capacity,
                         t.name as trainer_name, t.photo as trainer_photo,
                         (SELECT COUNT(*) FROM bookings 
                          WHERE schedule_id = s.id AND status = 'booked') as booked_count
                  FROM schedules s
                  JOIN classes c ON s.class_id = c.id
                  LEFT JOIN trainers t ON c.trainer_id = t.id
                  WHERE c.status = 'active'
                  ORDER BY FIELD(s.day_of_week, 
                         'Monday', 'Tuesday', 'Wednesday', 'Thursday', 
                         'Friday', 'Saturday', 'Sunday'),
                  s.start_time";

$schedules = $db->fetchAll($schedule_query);

// Organize schedules by day
$schedule_by_day = [];
foreach ($schedules as $schedule) {
    $schedule_by_day[$schedule['day_of_week']][] = $schedule;
}

// Handle class booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_class'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        $_SESSION['error_message'] = "Please login to book classes.";
        header("location: login.php");
        exit;
    }

    $schedule_id = $_POST['schedule_id'];
    $user_id = $_SESSION["user_id"];

    // Check if user has active membership
    $membership_check = $db->fetchOne(
        "SELECT * FROM user_memberships 
         WHERE user_id = ? AND status = 'active' 
         AND end_date >= CURDATE()",
        [$user_id]
    );

    if (!$membership_check) {
        $_SESSION['error_message'] = "Active membership required to book classes.";
    } else {
        // Check if class is full
        $schedule = $db->fetchOne(
            "SELECT s.*, c.capacity 
             FROM schedules s 
             JOIN classes c ON s.class_id = c.id 
             WHERE s.id = ?",
            [$schedule_id]
        );

        $current_bookings = $db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM bookings 
             WHERE schedule_id = ? AND status = 'booked'",
            [$schedule_id]
        )['count'];

        if ($current_bookings >= $schedule['capacity']) {
            $_SESSION['error_message'] = "This class is full.";
        } else {
            // Check if user already booked this class
            $existing_booking = $db->fetchOne(
                "SELECT * FROM bookings 
                 WHERE user_id = ? AND schedule_id = ? AND status = 'booked'",
                [$user_id, $schedule_id]
            );

            if ($existing_booking) {
                $_SESSION['error_message'] = "You have already booked this class.";
            } else {
                // Create booking
                $booking_query = "INSERT INTO bookings (user_id, schedule_id, booking_date, status) 
                                VALUES (?, ?, CURDATE(), 'booked')";

                if ($db->execute($booking_query, [$user_id, $schedule_id])) {
                    $_SESSION['success_message'] = "Class booked successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to book class. Please try again.";
                }
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<!-- Schedule Section -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4">Class Schedule</h1>
            <p class="lead">Find and book your favorite fitness classes</p>
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

        <!-- Schedule Tabs -->
        <ul class="nav nav-tabs mb-4" id="scheduleTab" role="tablist">
            <?php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $index => $day):
            ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>"
                        id="<?php echo strtolower($day); ?>-tab"
                        data-bs-toggle="tab"
                        href="#<?php echo strtolower($day); ?>"
                        role="tab">
                        <?php echo $day; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Schedule Content -->
        <div class="tab-content" id="scheduleTabContent">
            <?php foreach ($days as $index => $day): ?>
                <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>"
                    id="<?php echo strtolower($day); ?>"
                    role="tabpanel">

                    <?php if (isset($schedule_by_day[$day])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Class</th>
                                        <th>Trainer</th>
                                        <th>Room</th>
                                        <th>Availability</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedule_by_day[$day] as $class): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                echo date('g:i A', strtotime($class['start_time'])) . ' - ' .
                                                    date('g:i A', strtotime($class['end_time']));
                                                ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($class['class_name']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php
                                                    $description = htmlspecialchars($class['description']);
                                                    echo strlen($description) > 50 ?
                                                        substr($description, 0, 50) . '...' :
                                                        $description;
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($class['trainer_name']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($class['trainer_photo']): ?>
                                                            <img src="uploads/trainers/<?php echo htmlspecialchars($class['trainer_photo']); ?>"
                                                                alt="<?php echo htmlspecialchars($class['trainer_name']); ?>"
                                                                class="rounded-circle me-2" style="width: 30px; height: 30px;">
                                                        <?php endif; ?>
                                                        <?php echo htmlspecialchars($class['trainer_name']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">TBA</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($class['room']); ?></td>
                                            <td>
                                                <?php
                                                $available = $class['capacity'] - $class['booked_count'];
                                                $availability_class = $available > 5 ? 'success' : ($available > 0 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?php echo $availability_class; ?>">
                                                    <?php echo $available; ?> spots left
                                                </span>
                                            </td>
                                            <td>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                                    method="post">
                                                    <input type="hidden" name="schedule_id"
                                                        value="<?php echo $class['id']; ?>">
                                                    <button type="submit"
                                                        name="book_class"
                                                        class="btn btn-sm btn-primary"
                                                        <?php echo $available <= 0 ? 'disabled' : ''; ?>>
                                                        Book Now
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No classes scheduled for <?php echo $day; ?>.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>