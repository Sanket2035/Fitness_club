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

// Handle booking cancellation
if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $cancel_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?";
    $result = $db->execute($cancel_query, [$booking_id, $_SESSION["user_id"]]);
    
    if ($result) {
        $_SESSION['success_message'] = "Booking cancelled successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to cancel booking. Please try again.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get user's bookings with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total bookings count
$count_query = "SELECT COUNT(*) as total FROM bookings WHERE user_id = ?";
$total_bookings = $db->fetchOne($count_query, [$_SESSION["user_id"]])['total'];
$total_pages = ceil($total_bookings / $items_per_page);

// Get bookings with class and schedule details
$bookings_query = "SELECT b.*, c.name as class_name, c.description as class_description, 
                          s.start_time, s.end_time, s.day_of_week, s.room,
                          t.name as trainer_name
                   FROM bookings b 
                   JOIN schedules s ON b.schedule_id = s.id 
                   JOIN classes c ON s.class_id = c.id 
                   LEFT JOIN trainers t ON c.trainer_id = t.id
                   WHERE b.user_id = ? 
                   ORDER BY b.booking_date DESC, s.start_time ASC 
                   LIMIT ? OFFSET ?";

$bookings = $db->fetchAll($bookings_query, [$_SESSION["user_id"], $items_per_page, $offset]);
?>

<?php include '../includes/header.php'; ?>

<!-- Bookings Section -->
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <h2>My Class Bookings</h2>
            <hr>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <?php if (empty($bookings)): ?>
                <div class="alert alert-info">
                    You haven't booked any classes yet. 
                    <a href="schedule.php" class="alert-link">View class schedule</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Class</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Trainer</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['class_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['class_description']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['day_of_week']); ?></td>
                                    <td>
                                        <?php 
                                        echo date('g:i A', strtotime($booking['start_time'])) . ' - ' . 
                                             date('g:i A', strtotime($booking['end_time']));
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['room']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch($booking['status']) {
                                            case 'booked':
                                                $status_class = 'badge bg-success';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'badge bg-danger';
                                                break;
                                            case 'completed':
                                                $status_class = 'badge bg-info';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo $status_class; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] === 'booked'): ?>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                                                  method="post" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="cancel_booking" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Bookings pagination">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>