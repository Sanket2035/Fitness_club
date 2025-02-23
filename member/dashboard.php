<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Initialize Auth class
$auth = new Auth();

// Check if the user is logged in
if (!$auth->isLoggedIn()) {
    header("location: ../login.php");
    exit;
}

// Get user information using Auth class
$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
    header("location: ../login.php");
    exit;
}

$db = Database::getInstance();

// Get user's active membership
$membership = $db->fetchOne(
    "SELECT m.*, p.name as plan_name, p.description as plan_description 
     FROM user_memberships m 
     JOIN membership_plans p ON m.plan_id = p.id 
     WHERE m.user_id = ? AND m.status = 'active'",
    [$currentUser['id']]
);

// Get user's upcoming bookings
$upcoming_classes = $db->fetchAll(
    "SELECT b.*, c.name as class_name, s.start_time, s.day_of_week, s.room 
     FROM bookings b 
     JOIN schedules s ON b.schedule_id = s.id 
     JOIN classes c ON s.class_id = c.id 
     WHERE b.user_id = ? AND b.status = 'booked' 
     ORDER BY s.day_of_week, s.start_time 
     LIMIT 5",
    [$currentUser['id']]
);
?>

<?php include '../includes/header.php'; ?>

<!-- Dashboard Section -->
<div class="container py-5">
    <div class="row">
        <!-- Welcome Card -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2>Welcome back, <?php echo htmlspecialchars($currentUser['name']); ?>!</h2>
                    <p class="text-muted">Here's an overview of your fitness journey</p>
                </div>
            </div>
        </div>

        <!-- Membership Status -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Membership Status</h5>
                </div>
                <div class="card-body">
                    <?php if ($membership): ?>
                        <h4><?php echo htmlspecialchars($membership['plan_name']); ?></h4>
                        <p><?php echo htmlspecialchars($membership['plan_description']); ?></p>
                        <p><strong>Valid Until:</strong> <?php echo date('F d, Y', strtotime($membership['end_date'])); ?></p>
                        <a href="member/membership.php" class="btn btn-outline-primary">View Details</a>
                    <?php else: ?>
                        <p>No active membership found.</p>
                        <a href="../pricing.php" class="btn btn-primary">Get Membership</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Upcoming Classes</h5>
                </div>
                <div class="card-body">
                    <?php if ($upcoming_classes): ?>
                        <div class="list-group">
                            <?php foreach ($upcoming_classes as $class): ?>
                                <div class="list-group-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($class['class_name']); ?></h6>
                                    <p class="mb-1">
                                        <?php echo htmlspecialchars($class['day_of_week']); ?> at
                                        <?php echo date('g:i A', strtotime($class['start_time'])); ?>
                                    </p>
                                    <small>Room: <?php echo htmlspecialchars($class['room']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="member/bookings.php" class="btn btn-outline-success mt-3">View All Bookings</a>
                    <?php else: ?>
                        <p>No upcoming classes.</p>
                        <a href="schedule.php" class="btn btn-success">Book a Class</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="member/profile.php" class="btn btn-outline-primary btn-block w-100">
                                <i class="fas fa-user"></i> Edit Profile
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="schedule.php" class="btn btn-outline-success btn-block w-100">
                                <i class="fas fa-calendar"></i> Class Schedule
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="member/bookings.php" class="btn btn-outline-info btn-block w-100">
                                <i class="fas fa-bookmark"></i> My Bookings
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="member/membership.php" class="btn btn-outline-warning btn-block w-100">
                                <i class="fas fa-star"></i> Membership
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>