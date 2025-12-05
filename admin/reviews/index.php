<?php
//  Moderate Reviews (2.5)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../public/login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Moderate Reviews";
$is_admin = true;
$css_path = '../../assets/css/style.css';

// approval 
$filter = sanitizeString($_GET['filter'] ?? 'pending');
$allowed_filters = ['all', 'pending', 'approved'];
if (!in_array($filter, $allowed_filters)) {
    $filter = 'pending';
}

//query for filter
$sql = "
    SELECT r.*, c.class_name, c.class_id
    FROM reviews r
    INNER JOIN classes c ON r.class_id = c.class_id
";

if ($filter === 'pending') {
    $sql .= " WHERE r.is_approved = 0";
} elseif ($filter === 'approved') {
    $sql .= " WHERE r.is_approved = 1";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->query($sql);
$reviews = $stmt->fetchAll();

// Get counts
$stmt = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0");
$pending_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 1");
$approved_count = $stmt->fetchColumn();

$total_count = $pending_count + $approved_count;

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="fas fa-comments"></i> Moderate Reviews</h1>
        <p class="text-muted">Manage user-submitted class reviews</p>
    </div>
    <div class="col-md-4 text-right">
        <span class="badge badge-warning badge-lg"><?= $pending_count ?> Pending</span>
        <span class="badge badge-success badge-lg"><?= $approved_count ?> Approved</span>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <div class="btn-group" role="group">
            <a href="?filter=pending" 
               class="btn btn-<?= $filter === 'pending' ? 'warning' : 'outline-warning' ?>">
                <i class="fas fa-clock"></i> Pending (<?= $pending_count ?>)
            </a>
            <a href="?filter=approved" 
               class="btn btn-<?= $filter === 'approved' ? 'success' : 'outline-success' ?>">
                <i class="fas fa-check"></i> Approved (<?= $approved_count ?>)
            </a>
            <a href="?filter=all" 
               class="btn btn-<?= $filter === 'all' ? 'primary' : 'outline-primary' ?>">
                <i class="fas fa-list"></i> All (<?= $total_count ?>)
            </a>
        </div>
    </div>
</div>

<!-- Reviews List -->
<?php if (count($reviews) > 0): ?>
    <?php foreach ($reviews as $review): ?>
        <div class="card mb-3">
            <div class="card-header <?= $review['is_approved'] ? 'bg-success text-white' : 'bg-warning' ?>">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="fas fa-comment"></i>
                            Review for: <a href="../../public/class-detail.php?id=<?= $review['class_id'] ?>" 
                                          target="_blank"
                                          class="<?= $review['is_approved'] ? 'text-white' : 'text-dark' ?>">
                                <?= sanitizeString($review['class_name']) ?>
                            </a>
                        </h5>
                        <small>
                            Status: 
                            <?php if ($review['is_approved']): ?>
                                <strong>✓ Approved</strong>
                            <?php else: ?>
                                <strong>⏳ Pending</strong>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="col-md-4 text-right">
                        <small>ID: <?= $review['review_id'] ?></small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-9">
                        <div class="mb-2">
                            <strong>Reviewer:</strong> <?= sanitizeString($review['member_name']) ?>
                            <span class="ml-3">
                                <strong>Rating:</strong> 
                                <?php for ($i = 0; $i < $review['review_rating']; $i++): ?>
                                    <i class="fas fa-star text-warning"></i>
                                <?php endfor; ?>
                                <?php for ($i = $review['review_rating']; $i < 5; $i++): ?>
                                    <i class="far fa-star text-warning"></i>
                                <?php endfor; ?>
                                (<?= $review['review_rating'] ?>/5)
                            </span>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Review:</strong>
                            <p class="mb-0"><?= nl2br(sanitizeString($review['review_text'])) ?></p>
                        </div>
                        
                        <div class="text-muted small">
                            <?php if ($review['difficulty_accurate']): ?>
                                <span class="badge badge-info">Difficulty Accurate</span>
                            <?php endif; ?>
                            <?php if ($review['would_recommend']): ?>
                                <span class="badge badge-success">Would Recommend</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-muted small mt-2">
                            <i class="fas fa-clock"></i> Submitted: <?= formatDateTime($review['created_at']) ?>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="btn-group-vertical w-100">
                            <?php if (!$review['is_approved']): ?>
                                <a href="moderate.php?id=<?= $review['review_id'] ?>&action=approve" 
                                   class="btn btn-success btn-sm mb-2">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                            <?php else: ?>
                                <a href="moderate.php?id=<?= $review['review_id'] ?>&action=unapprove" 
                                   class="btn btn-warning btn-sm mb-2">
                                    <i class="fas fa-times"></i> Unapprove
                                </a>
                            <?php endif; ?>
                            
                            <a href="moderate.php?id=<?= $review['review_id'] ?>&action=disemvowel" 
                               class="btn btn-info btn-sm mb-2"
                               onclick="return confirm('Remove vowels from this review?')">
                                <i class="fas fa-filter"></i> Disemvowel
                            </a>
                            
                            <a href="moderate.php?id=<?= $review['review_id'] ?>&action=delete" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Permanently delete this review?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        No <?= $filter !== 'all' ? $filter : '' ?> reviews found.
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>