<?php
// Admin Dashboard
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

// Simple authentication for Week 11 (will improve in Week 14)
initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Admin Dashboard";
$is_admin = true;

// Get statistics
$stats = [];

// Total classes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM classes");
$stats['total_classes'] = $stmt->fetch()['total'];

// Active classes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM classes WHERE is_active = 1");
$stats['active_classes'] = $stmt->fetch()['total'];

// Total categories
$stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
$stats['total_categories'] = $stmt->fetch()['total'];

// Pending reviews
$stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews WHERE is_approved = 0");
$stats['pending_reviews'] = $stmt->fetch()['total'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// Recent classes
$stmt = $pdo->query("
    SELECT c.*, cat.category_name 
    FROM classes c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    ORDER BY c.created_at DESC
    LIMIT 5
");
$recent_classes = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
        </h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Classes</h6>
                        <h2 class="mb-0"><?= $stats['total_classes'] ?></h2>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-calendar fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-primary border-0">
                <a href="classes/index.php" class="text-white small">View All <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Active Classes</h6>
                        <h2 class="mb-0"><?= $stats['active_classes'] ?></h2>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success border-0">
                <a href="classes/index.php?filter=active" class="text-white small">View Active <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Categories</h6>
                        <h2 class="mb-0"><?= $stats['total_categories'] ?></h2>
                    </div>
                    <div class="text-dark-50">
                        <i class="fas fa-tags fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-warning border-0">
                <a href="categories/index.php" class="text-dark small">Manage Categories <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Pending Reviews</h6>
                        <h2 class="mb-0"><?= $stats['pending_reviews'] ?></h2>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-comments fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-info border-0">
                <a href="reviews/index.php" class="text-white small">Moderate Reviews <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="classes/create.php" class="btn btn-primary mr-2 mb-2">
                    <i class="fas fa-plus"></i> Add New Class
                </a>
                <a href="categories/create.php" class="btn btn-success mr-2 mb-2">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
                <a href="users/create.php" class="btn btn-info mr-2 mb-2">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
                <a href="../public/index.php" class="btn btn-secondary mb-2" target="_blank">
                    <i class="fas fa-eye"></i> View Public Site
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Classes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Recently Added Classes</h5>
            </div>
            <div class="card-body">
                <?php if (count($recent_classes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Instructor</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_classes as $class): ?>
                                    <tr>
                                        <td>
                                            <strong><?= sanitizeString($class['class_name']) ?></strong>
                                        </td>
                                        <td><?= sanitizeString($class['instructor_name']) ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= sanitizeString($class['category_name'] ?? 'Uncategorized') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($class['is_active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($class['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="classes/index.php" class="btn btn-primary btn-sm">View All Classes →</a>
                <?php else: ?>
                    <p class="text-muted mb-0">No classes added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>