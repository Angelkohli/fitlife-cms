<?php
// Admin - View All Classes
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Manage Classes";
$is_admin = true;
$css_path = '../../assets/css/style.css';

// Get sort parameter and validate (Feature 4.2 - ID sanitization)
$allowed_sort = ['class_name', 'created_at', 'updated_at'];
$sort_by = sanitizeString($_GET['sort'] ?? 'created_at');

// Validate sort parameter
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'created_at';
}

// Get sort order (ascending or descending)
$order = sanitizeString($_GET['order'] ?? 'DESC');
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

// Toggle order for next click
$next_order = ($order === 'ASC') ? 'DESC' : 'ASC';

// Build query with sorting
$sql = "
    SELECT c.*, cat.category_name 
    FROM classes c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    ORDER BY c.$sort_by $order
";

$stmt = $pdo->query($sql);
$classes = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="fas fa-calendar"></i> Manage Classes</h1>
        <p class="text-muted">Total Classes: <?= count($classes) ?></p>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Class
        </a>
    </div>
</div>

<!-- Sorting Controls (Feature 2.3) -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <strong><i class="fas fa-sort"></i> Sort By:</strong>
            </div>
            <div class="col-md-9">
                <div class="btn-group" role="group">
                    <a href="?sort=class_name&order=<?= ($sort_by === 'class_name') ? $next_order : 'ASC' ?>" 
                       class="btn btn-<?= ($sort_by === 'class_name') ? 'primary' : 'outline-primary' ?>">
                        Class Name
                        <?php if ($sort_by === 'class_name'): ?>
                            <i class="fas fa-sort-<?= strtolower($order) === 'asc' ? 'up' : 'down' ?>"></i>
                        <?php endif; ?>
                    </a>
                    
                    <a href="?sort=created_at&order=<?= ($sort_by === 'created_at') ? $next_order : 'DESC' ?>" 
                       class="btn btn-<?= ($sort_by === 'created_at') ? 'primary' : 'outline-primary' ?>">
                        Date Created
                        <?php if ($sort_by === 'created_at'): ?>
                            <i class="fas fa-sort-<?= strtolower($order) === 'asc' ? 'up' : 'down' ?>"></i>
                        <?php endif; ?>
                    </a>
                    
                    <a href="?sort=updated_at&order=<?= ($sort_by === 'updated_at') ? $next_order : 'DESC' ?>" 
                       class="btn btn-<?= ($sort_by === 'updated_at') ? 'primary' : 'outline-primary' ?>">
                        Date Updated
                        <?php if ($sort_by === 'updated_at'): ?>
                            <i class="fas fa-sort-<?= strtolower($order) === 'asc' ? 'up' : 'down' ?>"></i>
                        <?php endif; ?>
                    </a>
                </div>
                
                <span class="ml-3 text-muted">
                    <small>
                        Currently sorting by: 
                        <strong><?= ucwords(str_replace('_', ' ', $sort_by)) ?></strong> 
                        (<?= $order === 'ASC' ? 'Ascending' : 'Descending' ?>)
                    </small>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if (count($classes) > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Class Name</th>
                            <th>Instructor</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?= $class['class_id'] ?></td>
                                <td>
                                    <strong><?= sanitizeString($class['class_name']) ?></strong>
                                    <br>
                                </td>
                                <td><?= sanitizeString($class['instructor_name']) ?></td>
                                <td>
                                    <?php if ($class['category_name']): ?>
                                        <span class="badge badge-info">
                                            <?= sanitizeString($class['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($class['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                    <?php if ($class['is_featured']): ?>
                                        <span class="badge badge-warning">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $class['class_id'] ?>" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $class['class_id'] ?>" 
                                           class="btn btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this class?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No classes found. 
        <a href="create.php">Add your first class</a>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>