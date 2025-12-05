<?php
//  Browse Classes by Category (2.8)
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

$pdo = getDBConnection();
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';

// Get and validate category ID (4.2)
$category_id = sanitizeID($_GET['id'] ?? 0);

if (!$category_id) {
    setFlashMessage('Invalid category', 'error');
    header('Location: index.php');
    exit;
}

// Fetch category details
$stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = :id");
$stmt->execute([':id' => $category_id]);
$category = $stmt->fetch();

if (!$category) {
    setFlashMessage('Category not found', 'error');
    header('Location: index.php');
    exit;
}

$page_title = $category['category_name'] . " Classes";

// Fetch all active classes in this category
$stmt = $pdo->prepare("
    SELECT c.*, cat.category_name, cat.color_code
    FROM classes c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    WHERE c.category_id = :category_id AND c.is_active = 1
    ORDER BY c.class_id ASC
");
$stmt->execute([':category_id' => $category_id]);
$classes = $stmt->fetchAll();

// Fetch all categories for navigation
$stmt = $pdo->query("
    SELECT cat.*, COUNT(c.class_id) as class_count
    FROM categories cat
    LEFT JOIN classes c ON cat.category_id = c.category_id AND c.is_active = 1
    GROUP BY cat.category_id
    ORDER BY cat.display_order, cat.category_name
");
$all_categories = $stmt->fetchAll();

include '../includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="classes.php">All Classes</a></li>
        <li class="breadcrumb-item active"><?= sanitizeString($category['category_name']) ?></li>
    </ol>
</nav>

<!-- Category Header -->
<div class="jumbotron" style="background: linear-gradient(135deg, <?= sanitizeString($category['color_code']) ?> 0%, <?= sanitizeString($category['color_code']) ?>dd 100%);">
    <div class="container text-white text-center">
        <i class="fas <?= sanitizeString($category['category_icon']) ?> fa-4x mb-3"></i>
        <h1 class="display-4"><?= sanitizeString($category['category_name']) ?> Classes</h1>
        <p class="lead"><?= sanitizeString($category['category_description']) ?></p>
        <p class="mb-0">
            <span class="badge badge-light badge-lg">
                <?= count($classes) ?> <?= count($classes) === 1 ? 'Class' : 'Classes' ?> Available
            </span>
        </p>
    </div>
</div>

<!-- Category Navigation -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-th"></i> Browse Other Categories</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($all_categories as $cat): ?>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="category.php?id=<?= $cat['category_id'] ?>" 
                               class="text-decoration-none">
                                <div class="card h-100 text-center <?= $cat['category_id'] == $category_id ? 'border-primary' : '' ?>"
                                     style="<?= $cat['category_id'] == $category_id ? 'border-width: 3px;' : '' ?>">
                                    <div class="card-body p-2">
                                        <i class="fas <?= $cat['category_icon'] ?> fa-2x mb-2" 
                                           style="color: <?= $cat['color_code'] ?>"></i>
                                        <h6 class="card-title small mb-1"><?= sanitizeString($cat['category_name']) ?></h6>
                                        <small class="text-muted"><?= $cat['class_count'] ?> classes</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Classes in this Category -->
<?php if (count($classes) > 0): ?>
    <div class="row">
        <?php foreach ($classes as $class): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm" style="border-top: 4px solid <?= sanitizeString($category['color_code']) ?>">
                    <?php if ($class['instructor_image_path']): ?>
                        <img src="../admin/uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
                             class="card-img-top" 
                             alt="<?= sanitizeString($class['instructor_name']) ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                             style="height: 200px; background: linear-gradient(135deg, <?= sanitizeString($category['color_code']) ?>33 0%, <?= sanitizeString($category['color_code']) ?>66 100%);">
                            <i class="fas <?= sanitizeString($category['category_icon']) ?> fa-4x" 
                               style="color: <?= sanitizeString($category['color_code']) ?>"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="mb-2">
                            <?php if ($class['is_featured']): ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="card-title"><?= sanitizeString($class['class_name']) ?></h5>
                        
                        <p class="card-text text-muted small">
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
    
    <div class="text-center mb-4">
        <a href="classes.php" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-th"></i> View All Classes
        </a>
        <a href="search.php" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-search"></i> Search Classes
        </a>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        No classes are currently available in the <strong><?= sanitizeString($category['category_name']) ?></strong> category.
        <br><br>
        <a href="classes.php" class="btn btn-primary">Browse All Classes</a>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>