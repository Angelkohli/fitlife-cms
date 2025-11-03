<?php
// Public - Class Detail Page
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

$pdo = getDBConnection();
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';

// Get and validate class ID
$class_id = sanitizeID($_GET['id'] ?? 0);

if (!$class_id) {
    setFlashMessage('Invalid class ID', 'error');
    header('Location: classes.php');
    exit;
}

// Fetch class details with category
$stmt = $pdo->prepare("
    SELECT c.*, cat.category_name, cat.category_description, cat.color_code
    FROM classes c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    WHERE c.class_id = :class_id AND c.is_active = 1
");
$stmt->execute([':class_id' => $class_id]);
$class = $stmt->fetch();

if (!$class) {
    setFlashMessage('Class not found or inactive', 'error');
    header('Location: classes.php');
    exit;
}

$page_title = $class['class_name'];

// Fetch approved reviews for this class
$stmt = $pdo->prepare("
    SELECT * FROM reviews
    WHERE class_id = :class_id AND is_approved = 1
    ORDER BY created_at DESC
");
$stmt->execute([':class_id' => $class_id]);
$reviews = $stmt->fetchAll();

// Calculate average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $total_rating = array_sum(array_column($reviews, 'review_rating'));
    $avg_rating = round($total_rating / count($reviews), 1);
}

include '../includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="classes.php">All Classes</a></li>
        <li class="breadcrumb-item active"><?= sanitizeString($class['class_name']) ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Main Class Info -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <?php if ($class['instructor_image_path']): ?>
                <img src="../uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
                     class="card-img-top" 
                     alt="<?= sanitizeString($class['instructor_name']) ?>"
                     style="max-height: 400px; object-fit: cover;">
            <?php endif; ?>
            
            <div class="card-body">
                <div class="mb-3">
                    <?php if ($class['category_name']): ?>
                        <span class="badge badge-info badge-lg">
                            <?= sanitizeString($class['category_name']) ?>
                        </span>
                    <?php endif; ?>
                    <span class="badge badge-<?= getDifficultyBadgeColor($class['difficulty_level']) ?> badge-lg">
                        <?= $class['difficulty_level'] ?>
                    </span>
                    <?php if ($class['is_featured']): ?>
                        <span class="badge badge-warning badge-lg">
                            <i class="fas fa-star"></i> Featured
                        </span>
                    <?php endif; ?>
                </div>
                
                <h1 class="mb-3"><?= sanitizeString($class['class_name']) ?></h1>
                
                <?php if (count($reviews) > 0): ?>
                    <div class="mb-3">
                        <span class="text-warning">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <?php if ($i < floor($avg_rating)): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i < ceil($avg_rating)): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </span>
                        <span class="text-muted"><?= $avg_rating ?> / 5.0 (<?= count($reviews) ?> reviews)</span>
                    </div>
                <?php endif; ?>
                
                <p class="lead"><?= nl2br(sanitizeString($class['class_description'])) ?></p>
                
                <?php if ($class['equipment_needed']): ?>
                    <div class="alert alert-info">
                        <strong><i class="fas fa-toolbox"></i> Equipment Needed:</strong> 
                        <?= sanitizeString($class['equipment_needed']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-comments"></i> Member Reviews (<?= count($reviews) ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="media mb-4">
                            <div class="media-body">
                                <h6 class="mt-0 mb-1">
                                    <?= sanitizeString($review['member_name']) ?>
                                    <small class="text-muted">- <?= formatDateTime($review['created_at']) ?></small>
                                </h6>
                                <div class="text-warning mb-2">
                                    <?php for ($i = 0; $i < $review['review_rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    <?php for ($i = $review['review_rating']; $i < 5; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="mb-1"><?= nl2br(sanitizeString($review['review_text'])) ?></p>
                                <?php if ($review['would_recommend']): ?>
                                    <small class="text-success">
                                        <i class="fas fa-thumbs-up"></i> Would recommend
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No reviews yet. Be the first to review this class!</p>
                <?php endif; ?>
                
                <a href="#comment-form" class="btn btn-primary mt-3">
                    <i class="fas fa-comment"></i> Leave a Review
                </a>
            </div>
        </div>
        
        <!-- Comment Form Placeholder -->
        <div class="card mt-4" id="comment-form">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-edit"></i> Leave a Review</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Comment submission will be enabled in Week 13 (Feature 2.9)
                </p>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Schedule Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Schedule</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong><i class="fas fa-calendar-day"></i> Day:</strong><br>
                    <?= $class['day_of_week'] ?>s
                </div>
                <div class="mb-3">
                    <strong><i class="fas fa-clock"></i> Time:</strong><br>
                    <?= formatTime($class['start_time']) ?>
                </div>
                <div class="mb-3">
                    <strong><i class="fas fa-hourglass-half"></i> Duration:</strong><br>
                    <?= $class['duration_minutes'] ?> minutes
                </div>
                <div class="mb-3">
                    <strong><i class="fas fa-map-marker-alt"></i> Location:</strong><br>
                    <?= $class['class_location'] ?>
                    <?php if ($class['room_number']): ?>
                        <br><small class="text-muted">Room: <?= sanitizeString($class['room_number']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <strong><i class="fas fa-users"></i> Capacity:</strong><br>
                    <?= $class['current_enrolled'] ?> / <?= $class['max_participants'] ?> enrolled
                    <?php 
                    $spots_left = $class['max_participants'] - $class['current_enrolled'];
                    if ($spots_left > 0): 
                    ?>
                        <br><small class="text-success"><?= $spots_left ?> spots available</small>
                    <?php else: ?>
                        <br><small class="text-danger">Class Full</small>
                    <?php endif; ?>
                </div>
                <?php if ($class['calories_burned_avg']): ?>
                    <div class="mb-0">
                        <strong><i class="fas fa-fire text-danger"></i> Calories Burned:</strong><br>
                        ~<?= $class['calories_burned_avg'] ?> calories (average)
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Instructor Card -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Instructor</h5>
            </div>
            <div class="card-body text-center">
                <?php if ($class['instructor_image_path']): ?>
                    <img src="../uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
                         class="rounded-circle mb-3" 
                         alt="<?= sanitizeString($class['instructor_name']) ?>"
                         style="width: 120px; height: 120px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width: 120px; height: 120px;">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                <?php endif; ?>
                <h5><?= sanitizeString($class['instructor_name']) ?></h5>
            </div>
        </div>
        
        <!-- Category Card -->
        <?php if ($class['category_name']): ?>
            <div class="card">
                <div class="card-header" style="background-color: <?= $class['color_code'] ?>; color: white;">
                    <h5 class="mb-0"><i class="fas fa-tag"></i> Category</h5>
                </div>
                <div class="card-body">
                    <h5><?= sanitizeString($class['category_name']) ?></h5>
                    <?php if ($class['category_description']): ?>
                        <p class="small text-muted mb-3">
                            <?= sanitizeString($class['category_description']) ?>
                        </p>
                    <?php endif; ?>
                    <a href="category.php?id=<?= $class['category_id'] ?>" class="btn btn-sm btn-outline-primary btn-block">
                        <i class="fas fa-th"></i> View All <?= sanitizeString($class['category_name']) ?> Classes
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>