<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Initialize the session
// session_start();

// Set page title
$pageTitle = "Welcome";

// Get database instance using singleton pattern
$db = Database::getInstance();

// Get featured classes
$featured_classes = $db->fetchAll(
    "SELECT c.*, t.name as trainer_name 
     FROM classes c 
     LEFT JOIN trainers t ON c.trainer_id = t.id 
     WHERE c.status = 'active' 
     LIMIT 6"
);

// Get featured trainers
$featured_trainers = $db->fetchAll(
    "SELECT * FROM trainers 
     WHERE status = 'active' 
     LIMIT 4"
);

include 'includes/header.php';
?>

<!-- Hero Section Start -->
<div class="hero-section" style="background-image: url('images/home.jpg');">
    <div class="container h-100">
        <div class="row align-items-center h-100">
            <div class="col-lg-7">
                <div class="hero-content text-white">
                    <h1 class="display-3 mb-4 wow fadeInUp">Transform Your Body, Transform Your Life</h1>
                    <p class="lead mb-4 wow fadeInUp" data-wow-delay="0.2s">
                        Join our fitness community and achieve your health goals with expert trainers and state-of-the-art facilities.
                    </p>
                    <div class="wow fadeInUp" data-wow-delay="0.4s">
                        <a href="admin/classes.php" class="btn btn-primary btn-lg me-3">Our Classes</a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Hero Section End -->

<!-- Features Section Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4 wow fadeInUp">
                <div class="feature-item text-center">
                    <div class="feature-icon">
                        <i class="fas fa-dumbbell fa-3x text-primary mb-3"></i>
                    </div>
                    <h4>Modern Equipment</h4>
                    <p>Access to state-of-the-art fitness equipment for effective workouts.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4 wow fadeInUp" data-wow-delay="0.2s">
                <div class="feature-item text-center">
                    <div class="feature-icon">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    </div>
                    <h4>Expert Trainers</h4>
                    <p>Professional trainers to guide and motivate you on your fitness journey.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4 wow fadeInUp" data-wow-delay="0.4s">
                <div class="feature-item text-center">
                    <div class="feature-icon">
                        <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                    </div>
                    <h4>Healthy Life</h4>
                    <p>Comprehensive programs for a balanced and healthy lifestyle.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Features Section End -->

<!-- Featured Classes Section Start -->
<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5 wow fadeInUp">
            <h2 class="display-4">Featured Classes</h2>
            <p class="lead">Discover our popular fitness classes</p>
        </div>
        <div class="row">
            <?php foreach ($featured_classes as $class): ?>
                <div class="col-lg-4 col-md-6 mb-4 wow fadeInUp">
                    <div class="class-card card h-100">
                        <img src="<?php echo $class['image'] ? 'uploads/classes/' . htmlspecialchars($class['image']) : 'images/default-class.jpg'; ?>"
                            class="card-img-top" alt="<?php echo htmlspecialchars($class['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($class['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($class['description'], 0, 100)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <?php echo $class['trainer_name'] ? htmlspecialchars($class['trainer_name']) : 'TBA'; ?>
                                </span>
                                <span class="text-muted">
                                    <i class="fas fa-clock me-2"></i>
                                    <?php echo $class['duration']; ?> mins
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="schedule.php" class="btn btn-primary w-100">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="admin/classes.php" class="btn btn-lg btn-outline-primary">View All Classes</a>
        </div>
    </div>
</div>
<!-- Featured Classes Section End -->

<!-- Featured Trainers Section Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mb-5 wow fadeInUp">
            <h2 class="display-4">Expert Trainers</h2>
            <p class="lead">Meet our professional fitness trainers</p>
        </div>
        <div class="row">
            <?php foreach ($featured_trainers as $trainer): ?>
                <div class="col-lg-3 col-md-6 mb-4 wow fadeInUp">
                    <div class="trainer-card card h-100">
                        <img src="<?php echo $trainer['photo'] ? 'uploads/trainers/' . htmlspecialchars($trainer['photo']) : 'images/default-trainer.jpg'; ?>"
                            class="card-img-top" alt="<?php echo htmlspecialchars($trainer['name']); ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($trainer['name']); ?></h5>
                            <p class="text-primary"><?php echo htmlspecialchars($trainer['specialization']); ?></p>
                            <p class="card-text small"><?php echo htmlspecialchars(substr($trainer['bio'], 0, 100)) . '...'; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="admin/trainers.php" class="btn btn-lg btn-outline-primary">View All Trainers</a>
        </div>
    </div>
</div>
<!-- Featured Trainers Section End -->

<!-- Call to Action Section Start -->
<div class="container-fluid py-5 bg-primary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-white">
                <h2 class="display-4 mb-4">Ready to Start Your Fitness Journey?</h2>
                <p class="lead mb-0">Join now and get special offers on membership plans!</p>
            </div>
            <div class="col-lg-4 text-center">
                <a href="pricing.php" class="btn btn-light btn-lg">View Membership Plans</a>
            </div>
        </div>
    </div>
</div>
<!-- Call to Action Section End -->

<?php include 'includes/footer.php'; ?>