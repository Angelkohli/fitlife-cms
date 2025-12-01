<?php
// Public - Class Detail Page
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

require_once '../includes/captcha.php';

$pdo = getDBConnection();
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';

// Initialize session and check if user is logged in
initSession();
$is_logged_in = isLoggedIn();
$current_user_name = $is_logged_in ? $_SESSION['full_name'] : '';
$current_user_id = $is_logged_in ? $_SESSION['user_id'] : null;

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
                <img src="../admin/uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
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
        
        <!-- Comment Form (Feature 2.9 - 5 marks) -->
        <div class="card mt-4" id="comment-form">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-edit"></i> Leave a Review</h4>
            </div>
            <div class="card-body">
                <?php
                // Handle comment submission
                $comment_errors = [];
                $comment_success = false;
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
                    // Use logged-in user's name or form input for anonymous users
                    $member_name = $is_logged_in ? $current_user_name : sanitizeString($_POST['member_name'] ?? '');
                    
                    // Sanitize input
                    $comment_data = [
                        'member_name' => $member_name,
                        'review_text' => sanitizeString($_POST['review_text'] ?? ''),
                        'review_rating' => sanitizeID($_POST['review_rating'] ?? 0)
                    ];
                    
                    // Validate
                    $comment_errors = validateCommentData($comment_data);
                    
                    if (empty($comment_errors)) {
                        try {
                            $sql = "INSERT INTO reviews (
                                class_id, member_name, review_text, review_rating,
                                difficulty_accurate, would_recommend, is_approved
                            ) VALUES (
                                :class_id, :member_name, :review_text, :review_rating,
                                :difficulty_accurate, :would_recommend, 0
                            )";
                            
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([
                                ':class_id' => $class_id,
                                ':member_name' => $comment_data['member_name'],
                                ':review_text' => $comment_data['review_text'],
                                ':review_rating' => $comment_data['review_rating'],
                                ':difficulty_accurate' => isset($_POST['difficulty_accurate']) ? 1 : 0,
                                ':would_recommend' => isset($_POST['would_recommend']) ? 1 : 0
                            ]);
                            
                            $comment_success = true;
                            
                        } catch (PDOException $e) {
                            $comment_errors[] = "Error submitting review: " . $e->getMessage();
                        }
                    }
                }
                ?>
                
                <?php if ($comment_success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Thank you for your review!</strong> 
                        Your review has been submitted and will appear after moderation by our staff.
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($comment_errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0">
                            <?php foreach ($comment_errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!$comment_success): ?>
                    <form method="POST" action="#comment-form">
                        <?php if ($is_logged_in): ?>
                            <!-- Logged in user - show their name -->
                            <div class="form-group">
                                <label>Your Name</label>
                                <p class="form-control-plaintext font-weight-bold">
                                    <i class="fas fa-user-check text-success"></i> 
                                    <?= sanitizeString($current_user_name) ?>
                                </p>
                                <input type="hidden" name="member_name" value="<?= sanitizeString($current_user_name) ?>">
                            </div>
                        <?php else: ?>
                            <!-- Not logged in - show name input field -->
                            <div class="form-group">
                                <label for="member_name">Your Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="member_name" 
                                       name="member_name"
                                       value="<?= isset($comment_data) ? $comment_data['member_name'] : '' ?>"
                                       required>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="review_rating">Rating *</label>
                            <select class="form-control" id="review_rating" name="review_rating" required>
                                <option value="">-- Select Rating --</option>
                                <option value="5">⭐⭐⭐⭐⭐ (5 stars - Excellent)</option>
                                <option value="4">⭐⭐⭐⭐ (4 stars - Very Good)</option>
                                <option value="3">⭐⭐⭐ (3 stars - Good)</option>
                                <option value="2">⭐⭐ (2 stars - Fair)</option>
                                <option value="1">⭐ (1 star - Poor)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="review_text">Your Review *</label>
                            <textarea class="form-control" 
                                      id="review_text" 
                                      name="review_text"
                                      rows="5"
                                      placeholder="Share your experience with this class..."
                                      required><?= isset($comment_data) ? $comment_data['review_text'] : '' ?></textarea>
                            <small class="form-text text-muted">Minimum 10 characters</small>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="difficulty_accurate" 
                                   name="difficulty_accurate">
                            <label class="form-check-label" for="difficulty_accurate">
                                The difficulty level was accurate
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="would_recommend" 
                                   name="would_recommend"
                                   checked>
                            <label class="form-check-label" for="would_recommend">
                                I would recommend this class to others
                            </label>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Your review will be reviewed by our staff before being published.
                        </div>
                        
                        <button type="submit" name="submit_review" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                <?php else: ?>
                    <a href="class-detail.php?id=<?= $class_id ?>" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Submit Another Review
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
    
        
        <!-- Instructor Card -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Instructor</h5>
            </div>
            <div class="card-body text-center">
                <?php if ($class['instructor_image_path']): ?>
                    <img src="../admin/uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
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