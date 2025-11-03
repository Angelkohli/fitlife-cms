<?php
// Public - Browse All Classes (Feature 2.7 - Navigation/Menu)
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
    ORDER BY c.day_of_week, c.start_time, c.class_name
");
$classes = $stmt->fetchAll();

// Group classes by day
$days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$classes_by_day = [];

foreach ($classes as $class) {
    $day = $class['day_of_week'];
    if (!isset($classes_by_day[$day])) {
        $classes_by_day[$day] = [];
    }
    $classes_by_day[$day][] = $class;
}

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

<!-- Filter by Location -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <strong><i class="fas fa-filter"></i> Quick Filters:</strong>
            </div>
            <div class="col-md-9">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="classes.php" class="btn btn-outline-primary active">All Locations</a>
                    <a href="?location=Downtown" class="btn btn-outline-primary">Downtown Only</a>
                    <a href="?location=St. Vital" class="btn btn-outline-primary">St. Vital Only</a>
                </div>
                <div class="btn-group btn-group-sm ml-3" role="group">
                    <a href="?level=Beginner" class="btn btn-outline-success">Beginner</a>
                    <a href="?level=Intermediate" class="btn btn-outline-warning">Intermediate</a>
                    <a href="?level=Advanced" class="btn btn-outline-danger">Advanced</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Classes organized by day -->
<?php foreach ($days_order as $day): ?>
    <?php if (isset($classes_by_day[$day]) && count($classes_by_day[$day]) > 0): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-day"></i> <?= $day ?>
                    <span class="badge badge-light float-right"><?= count($classes_by_day[$day]) ?> classes</span>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($classes_by_day[$day] as $class): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-left-thick" style="border-left: 4px solid <?= $class['color_code'] ?? '#007bff' ?>">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <?php if ($class['category_name']): ?>
                                            <span class="badge badge-info">
                                                <?= sanitizeString($class['category_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge badge-<?= getDifficultyBadgeColor($class['difficulty_level']) ?>">
                                            <?= $class['difficulty_level'] ?>
                                        </span>
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
                                        <div><i class="fas fa-clock"></i> <?= formatTime($class['start_time']) ?> (<?= $class['duration_minutes'] ?> min)</div>
                                        <div><i class="fas fa-user"></i> <?= sanitizeString($class['instructor_name']) ?></div>
                                        <div><i class="fas fa-map-marker-alt"></i> <?= $class['class_location'] ?></div>
                                        <div>
                                            <i class="fas fa-users"></i> 
                                            <?= $class['current_enrolled'] ?> / <?= $class['max_participants'] ?> enrolled
                                        </div>
                                        <?php if ($class['calories_burned_avg']): ?>
                                            <div><i class="fas fa-fire text-danger"></i> ~<?= $class['calories_burned_avg'] ?> cal</div>
                                        <?php endif; ?>
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
    <?php endif; ?>
<?php endforeach; ?>

<?php if (count($classes) === 0): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No classes are currently available. Please check back soon!
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>