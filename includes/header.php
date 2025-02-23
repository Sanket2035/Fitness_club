<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';

// Initialize the Auth class
$auth = new Auth();

// Check if user is logged in
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo isset($pageTitle) ? $pageTitle : 'Welcome'; ?></title>
    <!-- Setting a base address -->
    <base href="http://localhost/Fitness_club/">
    <!-- Favicon -->
    <link href="images/favicon.ico" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>

<body>
    <!-- Top Bar Start -->
    <div class="top-bar d-none d-md-block">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="top-bar-left">
                        <div class="text">
                            <i class="far fa-clock"></i>
                            <h2>8:00 AM - 9:00 PM</h2>
                            <p>Mon - Fri</p>
                        </div>
                        <div class="text">
                            <i class="fa fa-phone"></i>
                            <h2>+123 456 7890</h2>
                            <p>For Appointment</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="top-bar-right">
                        <?php if ($isLoggedIn): ?>
                            <div class="dropdown">
                                <button class="btn btn-custom dropdown-toggle" type="button" id="userDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($currentUser['name']); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="member/dashboard.php">
                                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item" href="member/profile.php">
                                            <i class="fas fa-user-circle me-2"></i>Profile</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="logout.php">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="social">
                                <a href="login.php" class="btn btn-custom">Login</a>
                                <a href="register.php" class="btn btn-custom">Register</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Bar End -->

    <!-- Nav Bar Start -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-custom sticky-top">
        <div class="container-fluid">
            <a href="index.php" class="navbar-brand">
                Fitness<span class="text-primary">Club</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse" aria-controls="navbarCollapse"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto">
                    <a href="index.php" class="nav-item nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="about.php" class="nav-item nav-link <?php echo $currentPage == 'about.php' ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                    <a href="admin/classes.php" class="nav-item nav-link <?php echo $currentPage == 'classes.php' ? 'active' : ''; ?>">
                        <i class="fas fa-dumbbell"></i> Classes
                    </a>
                    <a href="schedule.php" class="nav-item nav-link <?php echo $currentPage == 'schedule.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                    <a href="admin/trainers.php" class="nav-item nav-link <?php echo $currentPage == 'trainers.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-friends"></i> Trainers
                    </a>
                    <a href="pricing.php" class="nav-item nav-link <?php echo $currentPage == 'pricing.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i> Pricing
                    </a>
                    <a href="contact.php" class="nav-item nav-link <?php echo $currentPage == 'contact.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Nav Bar End -->

    <!-- Page Header Start -->
    <?php if (isset($pageTitle) && $currentPage != 'index.php'): ?>
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
                    </div>
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?php echo htmlspecialchars($pageTitle); ?>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- Page Header End -->

    <!-- Main Content Container Start -->
    <div class="container-fluid">