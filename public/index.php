<?php
// Public Homepage (Feature 2.7 - 5 marks)
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

$pdo = getDBConnection();
$page_title = "Home";
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';

// Fetch featured classes
$stmt = $pdo->query("
    SELECT c.*, cat.category_name, cat.color_code
    FROM classes c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    WHERE c.is_active = 1 AND c.is_featured = 1
    ORDER BY c.day_of_week, c.start_time
    LIMIT 6
");
$featured_classes = $stmt->fetchAll();

// Fetch all categories with class count
$stmt = $pdo->query("
    SELECT cat.*, COUNT(c.class_id) as class_count
    FROM categories cat
    LEFT JOIN classes c ON cat.category_id = c.category_id AND c.is_active = 1
    GROUP BY cat.category_id
    ORDER BY cat.display_order, cat.category_name
");
$categories = $stmt->fetchAll();

// Get total active classes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM classes WHERE is_active = 1");
$total_classes = $stmt->fetch()['total'];

include '../includes/header.php';
?>

<!-- Hero Section -->
<div class="jumbotron jumbotron-fluid bg-primary text-white mb-4">
    <div class="container text-center">
        <h1 class="display-3 mb-3">
            <i class="fas fa-dumbbell"></i> Welcome to FitLife Winnipeg
        </h1>
        <p class="lead mb-4">
            Transform Your Body, Transform Your Life
        </p>
        <p class="mb-4">
            Discover <?= $total_classes ?> amazing fitness classes across two convenient locations
        </p>
        <a href="classes.php" class="btn btn-light btn-lg mr-2">
            <i class="fas fa-calendar"></i> Browse All Classes
        </a>
        <a href="search.php" class="btn btn-outline-light btn-lg">
            <i class="fas fa-search"></i> Search Classes
        </a>
    </div>
</div>

<!-- Category Cards -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-tags"></i> Browse by Category
        </h2>
    </div>
    
    <?php foreach ($categories as $category): ?>
        <div class="col-md-4 col-lg-2 mb-3">
            <a href="category.php?id=<?= $category['category_id'] ?>" class="text-decoration-none">
                <div class="card h-100 text-center hover-shadow" style="border-top: 4px solid <?= $category['color_code'] ?>">
                    <div class="card-body">
                        <i class="fas <?= $category['category_icon'] ?> fa-3x mb-3" style="color: <?= $category['color_code'] ?>"></i>
                        <h5 class="card-title"><?= sanitizeString($category['category_name']) ?></h5>
                        <p class="text-muted mb-0">
                            <small><?= $category['class_count'] ?> classes</small>
                        </p>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Featured Classes -->
<?php if (count($featured_classes) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-star text-warning"></i> Featured Classes
            </h2>
        </div>
    </div>
    
    <div class="row">
        <?php foreach ($featured_classes as $class): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($class['instructor_image_path']): ?>
                        <img src="../uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
                             class="card-img-top" 
                             alt="<?= sanitizeString($class['instructor_name']) ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image fa-3x text-white"></i>
                        </div>
                    <?php endif; ?>
                    
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
                        </div>
                        
                        <h5 class="card-title"><?= sanitizeString($class['class_name']) ?></h5>
                        
                        <p class="card-text text-muted">
                            <?= truncateText($class['class_description'], 120) ?>
                        </p>
                        
                        <div class="small text-muted mb-3">
                            <div><i class="fas fa-user"></i> <?= sanitizeString($class['instructor_name']) ?></div>
                            <div><i class="fas fa-calendar-day"></i> <?= $class['day_of_week'] ?>s at <?= formatTime($class['start_time']) ?></div>
                            <div><i class="fas fa-map-marker-alt"></i> <?= $class['class_location'] ?></div>
                            <div><i class="fas fa-clock"></i> <?= $class['duration_minutes'] ?> minutes</div>
                            <?php if ($class['calories_burned_avg']): ?>
                                <div><i class="fas fa-fire"></i> ~<?= $class['calories_burned_avg'] ?> calories</div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="class-detail.php?id=<?= $class['class_id'] ?>" class="btn btn-primary btn-block">
                            <i class="fas fa-info-circle"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mb-5">
        <a href="classes.php" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-th"></i> View All Classes
        </a>
    </div>
<?php endif; ?>

<!-- Info Section -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Two Locations</h5>
                <p class="card-text">
                    Downtown & St. Vital locations for your convenience
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-users fa-3x text-success mb-3"></i>
                <h5 class="card-title">Expert Instructors</h5>
                <p class="card-text">
                    Certified professionals dedicated to your fitness journey
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                <h5 class="card-title">Flexible Schedule</h5>
                <p class="card-text">
                    Classes available 7 days a week at various times
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>