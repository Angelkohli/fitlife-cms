<?php
// Browse Classes (2.7)
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

$pdo = getDBConnection();
$page_title = "All Classes";
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';

// Fetch all active classes with category information 
$stmt = $pdo->query("
    SELECT c.*, cat.category_name, cat.color_code
    FROM classes c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    WHERE c.is_active = 1
    ORDER BY c.class_id ASC
");
$classes = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="fas fa-calendar"></i> All Classes</h1>
        <p class="text-muted">Browse all <?= count($classes) ?> available fitness classes</p>
    </div>
    <div class="col-md-4 text-right">
        <a href="search.php" class="btn btn-primary">
            <i class="fas fa-search"></i> Search Classes
        </a>
    </div>
</div>


<!-- Classes displayed in grid  -->
<?php if (count($classes) > 0): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-list"></i> All Fitness Classes
                <span class="badge badge-light float-right"><?= count($classes) ?> classes</span>
            </h4>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($classes as $class): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 border-left-thick" style="border-left: 4px solid <?= $class['color_code'] ?? '#007bff' ?>">
                            <div class="card-body">
                                <!-- Class ID Badge -->
                                <div class="mb-2">
                                    <span class="badge badge-secondary">ID: <?= $class['class_id'] ?></span>
                                    <?php if ($class['category_name']): ?>
                                        <span class="badge badge-info">
                                            <?= sanitizeString($class['category_name']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($class['is_featured']): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h5 class="card-title">
                                    <?= sanitizeString($class['class_name']) ?>
                                </h5>
                                
                                <div class="small text-muted mb-2">
                                    <div><i class="fas fa-user"></i> <?= sanitizeString($class['instructor_name']) ?></div>
                                </div>
                                
                                <p class="card-text small">
                                    <?= truncateText($class['class_description'], 100) ?>
                                </p>
                                
                                <a href="class-detail.php?id=<?= $class['class_id'] ?>" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-info-circle"></i> View Details & Reviews
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No classes are currently available. Please check back soon!
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>