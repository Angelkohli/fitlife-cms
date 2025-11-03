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

// Fetch all classes with category information
$stmt = $pdo->query("
    SELECT c.*, cat.category_name 
    FROM classes c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    ORDER BY c.created_at DESC
");
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
                            <th>Day & Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
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
                                    <span class="badge badge-<?= getDifficultyBadgeColor($class['difficulty_level']) ?>">
                                        <?= $class['difficulty_level'] ?>
                                    </span>
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
                                    <?= $class['day_of_week'] ?><br>
                                    <small class="text-muted"><?= formatTime($class['start_time']) ?></small>
                                </td>
                                <td><?= $class['class_location'] ?></td>
                                <td>
                                    <?= $class['current_enrolled'] ?> / <?= $class['max_participants'] ?>
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