<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>FitLife Winnipeg CMS</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= isset($css_path) ? $css_path : '../assets/css/style.css' ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= isset($is_admin) && $is_admin ? 'index.php' : '../public/index.php' ?>">
                <i class="fas fa-dumbbell"></i> FitLife Winnipeg
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Search Form (Feature 3.1) -->
                <?php if (!isset($is_admin) || !$is_admin): ?>
                    <form method="GET" action="<?= isset($is_admin) && $is_admin ? '../public/search.php' : 'search.php' ?>" class="form-inline mx-auto my-2 my-lg-0">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   name="q" 
                                   placeholder="Search classes..."
                                   style="width: 250px;">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-light">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
                
                <ul class="navbar-nav ml-auto">
                    <?php if (isset($is_admin) && $is_admin): ?>
                        <!-- Admin Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/classes/index.php"><i class="fas fa-calendar"></i> Classes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/categories/index.php"><i class="fas fa-tags"></i> Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/reviews/index.php"><i class="fas fa-comments"></i> Reviews</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/users/index.php"><i class="fas fa-users"></i> Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/public/index.php"><i class="fas fa-eye"></i> View Site</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="<?php echo BASE_URL; ?>/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    <?php else: ?>
                        <!-- Public Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="classes.php"><i class="fas fa-calendar"></i> Classes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search.php"><i class="fas fa-search"></i> Search</a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <span class="nav-link text-light">
                                    <i class="fas fa-user"></i> <?= sanitizeString($_SESSION['full_name'] ?? $_SESSION['username']) ?>
                                    <?php if (isAdmin()): ?>
                                        <span class="badge badge-warning ml-1">Admin</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-warning" href="<?php echo BASE_URL; ?>/public/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="register.php">
                                    <i class="fas fa-user-plus"></i> Register
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <main class="container mt-4 mb-5">
        <?php displayFlashMessage(); ?>