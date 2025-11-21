<?php
// Public - Search Classes (Feature 3.1 - 5 marks)
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

$pdo = getDBConnection();
$page_title = "Search Classes";
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';

$search_query = '';
$category_filter = '';
$results = [];
$searched = false;

// Pagination settings (Feature 3.3 - 5 marks)
$results_per_page = 6; // Easy to change for testing
$current_page = sanitizeID($_GET['page'] ?? 1);
if ($current_page < 1) $current_page = 1;

// Fetch all categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $stmt->fetchAll();

// Handle search
if (isset($_GET['q'])) {
    $search_query = sanitizeString($_GET['q'] ?? '');
    $category_filter = sanitizeID($_GET['category'] ?? 0);
    $searched = true;
    
    if (!empty($search_query)) {
        // Build query with optional category filter (Feature 3.2 - 5 marks)
        $sql = "
            SELECT c.*, cat.category_name, cat.color_code
            FROM classes c
            LEFT JOIN categories cat ON c.category_id = cat.category_id
            WHERE c.is_active = 1 
            AND (
                c.class_name LIKE :query1 
                OR c.class_description LIKE :query2 
                OR c.instructor_name LIKE :query3
            )
        ";

        // Add category filter if selected
        if ($category_filter) {
            $sql .= " AND c.category_id = :category_id";
        }
        
        $sql .= " ORDER BY c.class_name";

        $stmt = $pdo->prepare($sql);
         $params = ([
            ':query1' => '%' . $search_query . '%',
            ':query2' => '%' . $search_query . '%',
            ':query3' => '%' . $search_query . '%'
        ]);
        
        if ($category_filter) {
            $params[':category_id'] = $category_filter;
        }
        
        $stmt->execute($params);
        $results = $stmt->fetchAll();
    }
}

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-search"></i> Search Fitness Classes</h1>
        <p class="text-muted">Find the perfect class for your fitness goals</p>
    </div>
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="search.php" class="form-inline">
            <div class="input-group w-100">
                <input type="text" 
                       class="form-control form-control-lg" 
                       name="q" 
                       placeholder="Search by class name, instructor, or description..."
                       value="<?= htmlspecialchars($search_query) ?>"
                       autofocus>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
        
        <div class="mt-3">
            <strong>Quick searches:</strong>
            <a href="?q=yoga" class="badge badge-primary">Yoga</a>
            <a href="?q=cardio" class="badge badge-danger">Cardio</a>
            <a href="?q=strength" class="badge badge-warning">Strength</a>
            <a href="?q=beginner" class="badge badge-success">Beginner</a>
            <a href="?q=advanced" class="badge badge-info">Advanced</a>
        </div>
    </div>
</div>

<!-- Search Results -->
<?php if ($searched): ?>
    <?php if (!empty($search_query)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <?php if (count($results) > 0): ?>
                Found <strong><?= count($results) ?></strong> class<?= count($results) !== 1 ? 'es' : '' ?> 
                matching "<strong><?= htmlspecialchars($search_query) ?></strong>"
            <?php else: ?>
                No classes found matching "<strong><?= htmlspecialchars($search_query) ?></strong>"
            <?php endif; ?>
        </div>
        
        <?php if (count($results) > 0): ?>
            <div class="row">
                <?php foreach ($results as $class): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm" style="border-top: 4px solid <?= $class['color_code'] ?? '#007bff' ?>">
                            <?php if ($class['instructor_image_path']): ?>
                                <img src="../uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= sanitizeString($class['instructor_name']) ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                     style="height: 200px; background: linear-gradient(135deg, <?= $class['color_code'] ?? '#007bff' ?>33 0%, <?= $class['color_code'] ?? '#007bff' ?>66 100%);">
                                    <i class="fas fa-dumbbell fa-4x text-white"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="mb-2">
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
                                
                                <h5 class="card-title"><?= sanitizeString($class['class_name']) ?></h5>
                                
                                <p class="card-text text-muted small">
                                    <?= truncateText($class['class_description'], 100) ?>
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
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>No classes found</h4>
                    <p class="text-muted">Try different keywords or browse all classes</p>
                    <a href="classes.php" class="btn btn-primary">
                        <i class="fas fa-th"></i> Browse All Classes
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            Please enter a search term
        </div>
    <?php endif; ?>
<?php else: ?>
    <!-- Search Tips -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-lightbulb fa-3x text-warning mb-3"></i>
                    <h5>Search Tips</h5>
                    <p class="text-muted small">
                        Search by class name, instructor name, or equipment needed. 
                        Try terms like "yoga", "cardio", or specific instructor names.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-filter fa-3x text-primary mb-3"></i>
                    <h5>Browse by Category</h5>
                    <p class="text-muted small">
                        Looking for a specific type of class? 
                        <a href="index.php">Browse by category</a> to see all yoga, cardio, or strength classes.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar fa-3x text-success mb-3"></i>
                    <h5>View Full Schedule</h5>
                    <p class="text-muted small">
                        See all classes organized by day of the week. 
                        <a href="classes.php">View full schedule</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>