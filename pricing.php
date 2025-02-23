<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Initialize the session
// session_start();

// $db = new Database();
$db = Database::getInstance();

// Get all active membership plans
$plans_query = "SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC";
$plans = $db->fetchAll($plans_query);

// Handle plan purchase
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase_plan'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        $_SESSION['error_message'] = "Please login to purchase a membership.";
        header("location: login.php");
        exit;
    }

    $plan_id = $_POST['plan_id'];
    $user_id = $_SESSION["user_id"];

    // Check if user already has an active membership
    $active_membership = $db->fetchOne(
        "SELECT * FROM user_memberships 
         WHERE user_id = ? AND status = 'active' 
         AND end_date >= CURDATE()",
        [$user_id]
    );

    if ($active_membership) {
        $_SESSION['error_message'] = "You already have an active membership.";
    } else {
        // Get plan details
        $plan = $db->fetchOne("SELECT * FROM membership_plans WHERE id = ?", [$plan_id]);

        if ($plan) {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+{$plan['duration']} months"));

            // Create membership
            $insert_query = "INSERT INTO user_memberships 
                           (user_id, plan_id, start_date, end_date, status) 
                           VALUES (?, ?, ?, ?, 'active')";

            if ($db->execute($insert_query, [$user_id, $plan_id, $start_date, $end_date])) {
                $_SESSION['success_message'] = "Membership purchased successfully!";
                header("location: member/dashboard.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to process membership. Please try again.";
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<!-- Pricing Section -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4">Membership Plans</h1>
            <p class="lead">Choose the perfect membership plan for your fitness journey</p>
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
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <h4 class="my-0 fw-normal"><?php echo htmlspecialchars($plan['name']); ?></h4>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h1 class="card-title text-center mb-4">
                                $<?php echo number_format($plan['price'], 2); ?>
                                <small class="text-muted fw-light">/month</small>
                            </h1>

                            <div class="mb-4">
                                <?php
                                $features = json_decode($plan['features'], true);
                                if ($features):
                                ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($features as $feature): ?>
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>
                                                <?php echo htmlspecialchars($feature); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <p class="text-muted text-center mb-4">
                                    <?php echo $plan['duration']; ?> months commitment
                                </p>

                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                    method="post" class="text-center">
                                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                    <button type="submit"
                                        name="purchase_plan"
                                        class="btn btn-lg btn-outline-primary w-100">
                                        Select Plan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Additional Information -->
        <div class="row mt-5">
            <div class="col-md-8 mx-auto text-center">
                <h4>All Memberships Include</h4>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <i class="fas fa-dumbbell fa-2x mb-3 text-primary"></i>
                        <h5>Modern Equipment</h5>
                        <p>Access to state-of-the-art fitness equipment</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-shower fa-2x mb-3 text-primary"></i>
                        <h5>Locker Rooms</h5>
                        <p>Clean and well-maintained facilities</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-user-friends fa-2x mb-3 text-primary"></i>
                        <h5>Fitness Community</h5>
                        <p>Join a supportive fitness community</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>