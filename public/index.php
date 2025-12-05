<?php
// Public Homepage (2.7)
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

$pdo = getDBConnection();
$page_title = "Home";
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';

// Check if a category filter is selected
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Fetch featured classes
if ($selected_category > 0) {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.category_name, cat.color_code
        FROM classes c
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        WHERE c.is_active = 1 
        AND c.category_id = ?
        ORDER BY c.class_name
        LIMIT 12
    ");
    $stmt->execute([$selected_category]);
    $featured_classes = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT c.*, cat.category_name, cat.color_code
        FROM classes c
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        WHERE c.is_active = 1 AND c.is_featured = 1
        LIMIT 6
    ");
    $featured_classes = $stmt->fetchAll();
}

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

// Get selected category name for display
$selected_category_name = "All Categories";
if ($selected_category > 0) {
    foreach ($categories as $cat) {
        if ($cat['category_id'] == $selected_category) {
            $selected_category_name = sanitizeString($cat['category_name']);
            break;
        }
    }
}

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

<!-- Category Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-3">
                    <i class="fas fa-filter"></i> Filter Classes
                </h2>
                <form method="GET" action="" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="category" class="mr-2"><strong>Select Category:</strong></label>
                        <select name="category" id="category" class="form-control" onchange="this.form.submit()">
                            <option value="0" <?= $selected_category == 0 ? 'selected' : '' ?>>All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id'] ?>" 
                                    <?= $selected_category == $category['category_id'] ? 'selected' : '' ?>
                                    style="color: <?= $category['color_code'] ?>">
                                    <?= sanitizeString($category['category_name']) ?> 
                                    (<?= $category['class_count'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($selected_category > 0): ?>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear Filter
                        </a>
                    <?php endif; ?>
                </form>
                
                <?php if ($selected_category > 0): ?>
                    <div class="mt-3 alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Showing classes in category: <strong><?= $selected_category_name ?></strong>
                        <a href="category.php?id=<?= $selected_category ?>" class="btn btn-sm btn-info ml-2">
                            View Category Details
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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

<!-- Classes Display Section -->
<div class="row mb-4">
    <div class="col-12">
        <?php if ($selected_category > 0): ?>
            <h2 class="mb-4">
                <i class="fas <?= getCategoryIconById($categories, $selected_category) ?>" 
                   style="color: <?= getCategoryColorById($categories, $selected_category) ?>"></i>
                <?= $selected_category_name ?> Classes
            </h2>
        <?php else: ?>
            <h2 class="mb-4">
                <i class="fas fa-star text-warning"></i> Featured Classes
            </h2>
        <?php endif; ?>
    </div>
</div>

<?php if (count($featured_classes) > 0): ?>
    <div class="row">
        <?php foreach ($featured_classes as $class): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($class['instructor_image_path']): ?>
                        <img src="../admin/uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
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
                                <span class="badge badge-info" style="background-color: <?= $class['color_code'] ?>">
                                    <?= sanitizeString($class['category_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="card-title"><?= sanitizeString($class['class_name']) ?></h5>
                        
                        <p class="card-text text-muted">
                            <?= truncateText($class['class_description'], 120) ?>
                        </p>
                        
                        <div class="small text-muted mb-3">
                            <div><i class="fas fa-user"></i> <?= sanitizeString($class['instructor_name']) ?></div>
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
        <?php if ($selected_category > 0): ?>
            <a href="classes.php?category=<?= $selected_category ?>" class="btn btn-primary btn-lg mr-2">
                <i class="fas fa-th"></i> View All <?= $selected_category_name ?> Classes
            </a>
            <a href="index.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-star"></i> Back to Featured Classes
            </a>
        <?php else: ?>
            <a href="classes.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-th"></i> View All Classes
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        No classes found in this category. Please try another category or view all classes.
        <a href="classes.php" class="btn btn-outline-warning btn-sm ml-2">
            Browse All Classes
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

<?php
//   to get category icon by ID
function getCategoryIconById($categories, $category_id) {
    foreach ($categories as $cat) {
        if ($cat['category_id'] == $category_id) {
            return $cat['category_icon'];
        }
    }
    return 'fa-tag';
}

// to get category color by ID
function getCategoryColorById($categories, $category_id) {
    foreach ($categories as $cat) {
        if ($cat['category_id'] == $category_id) {
            return $cat['color_code'];
        }
    }
    return '#007bff';
}
?>