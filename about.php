<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Initialize the session
// session_start();
?>

<?php include 'includes/header.php'; ?>

<!-- About Section -->
<div class="container-fluid py-5">
    <div class="container">
        <!-- Main About Section -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <img src="images/about1.jpg" class="img-fluid rounded shadow-sm" alt="About Our Gym">
            </div>
            <div class="col-lg-6">
                <h1 class="display-4 mb-4">Welcome to Fitness Club</h1>
                <p class="lead">Transforming lives through fitness since 2010</p>
                <p>At Fitness Club, we believe in providing more than just a gym – we create a community dedicated to helping you achieve your fitness goals. Our state-of-the-art facility combines modern equipment, expert trainers, and innovative programs to deliver an unmatched fitness experience.</p>
                <div class="row mt-4">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check text-primary me-3"></i>
                            <h5 class="mb-0">Expert Trainers</h5>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check text-primary me-3"></i>
                            <h5 class="mb-0">Modern Equipment</h5>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check text-primary me-3"></i>
                            <h5 class="mb-0">Fitness Classes</h5>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check text-primary me-3"></i>
                            <h5 class="mb-0">Personal Training</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us Section -->
        <div class="text-center mb-5">
            <h2 class="display-5">Why Choose Us</h2>
            <p class="lead">Experience the difference at Fitness Club</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-dumbbell fa-3x text-primary mb-3"></i>
                        <h4>Modern Equipment</h4>
                        <p>State-of-the-art fitness equipment for effective workouts</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h4>Expert Trainers</h4>
                        <p>Certified professionals to guide your fitness journey</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h4>Flexible Hours</h4>
                        <p>Open early until late to fit your schedule</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                        <h4>Community</h4>
                        <p>Join a supportive community of fitness enthusiasts</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Mission Section -->
        <div class="row align-items-center mt-5">
            <div class="col-lg-6 order-lg-2">
                <img src="images/about2.jpg" class="img-fluid rounded shadow-sm" alt="Our Mission">
            </div>
            <div class="col-lg-6 order-lg-1">
                <h2 class="mb-4">Our Mission</h2>
                <p>Our mission is to inspire and empower our community to achieve their fitness goals through:</p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-primary me-2"></i>
                        Professional guidance and support
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-primary me-2"></i>
                        Innovative fitness programs
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-primary me-2"></i>
                        Welcoming and motivating environment
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-primary me-2"></i>
                        Continuous improvement and education
                    </li>
                </ul>
            </div>
        </div>

        <!-- Team Section -->
        <div class="text-center mt-5 mb-4">
            <h2 class="display-5">Our Team</h2>
            <p class="lead">Meet our dedicated fitness professionals</p>
        </div>

        <div class="row">
            <?php
            
            $db = Database::getInstance();

            // Get featured trainers
            $trainers_query = "SELECT * FROM trainers WHERE status = 'active' LIMIT 4";
            $trainers = $db->fetchAll($trainers_query);

            foreach ($trainers as $trainer):
            ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <img src="<?php echo $trainer['photo'] ? 'uploads/trainers/' . htmlspecialchars($trainer['photo']) : 'images/default-trainer.jpg'; ?>"
                            class="card-img-top"
                            alt="<?php echo htmlspecialchars($trainer['name']); ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($trainer['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($trainer['specialization']); ?></p>
                            <p class="card-text small"><?php echo htmlspecialchars($trainer['bio']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>