<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Initialize Auth class
$auth = new Auth();

// Check if the user is logged in and is an admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("location: ../login.php");
    exit;
}

$db = Database::getInstance();

// Get dashboard statistics
$stats = [
    'total_members' => $db->fetchOne(
        "SELECT COUNT(*) as count FROM users WHERE status = 'active' AND role = 'member'",
        []
    )['count'],
    'active_memberships' => $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_memberships WHERE status = 'active'",
        []
    )['count'],
    'total_classes' => $db->fetchOne(
        "SELECT COUNT(*) as count FROM classes",
        []
    )['count'],
    'total_bookings' => $db->fetchOne(
        "SELECT COUNT(*) as count FROM bookings WHERE status = 'booked'",
        []
    )['count']
];

// Get recent members
$recent_members = $db->fetchAll(
    "SELECT id, name, email, join_date 
     FROM users 
     WHERE role = 'member' AND status = 'active' 
     ORDER BY join_date DESC 
     LIMIT 5"
);

// Get upcoming classes
$upcoming_classes = $db->fetchAll(
    "SELECT c.name, s.day_of_week, s.start_time, s.room, 
            (SELECT COUNT(*) FROM bookings b 
             WHERE b.schedule_id = s.id AND b.status = 'booked') as booked_count
     FROM schedules s
     JOIN classes c ON s.class_id = c.id
     WHERE s.start_time >= CURDATE()
     ORDER BY s.day_of_week, s.start_time
     LIMIT 5"
);
?>

<?php include '../includes/admin_header.php'; ?>

<!-- Admin Dashboard -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2>Admin Dashboard</h2>
            <hr>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_members']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Memberships</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_memberships']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Classes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_classes']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dumbbell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_bookings']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Members -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Members</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Join Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($member['join_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Classes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcoming_classes as $class): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['name']); ?></td>
                                    <td><?php echo htmlspecialchars($class['day_of_week']); ?></td>
                                    <td><?php echo date('g:i A', strtotime($class['start_time'])); ?></td>
                                    <td><?php echo $class['booked_count']; ?></td>
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