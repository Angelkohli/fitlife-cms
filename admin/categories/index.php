<?php
// Admin - Manage Categories (Feature 2.4 - 5 marks)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../public/login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Manage Categories";
$is_admin = true;
$css_path = '../../assets/css/style.css';

// Fetch all categories with class count
$stmt = $pdo->query("
    SELECT cat.*, COUNT(c.class_id) as class_count
    FROM categories cat
    LEFT JOIN classes c ON cat.category_id = c.category_id
    GROUP BY cat.category_id
    ORDER BY cat.display_order, cat.category_name
");
$categories = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="fas fa-tags"></i> Manage Categories</h1>
        <p class="text-muted">Total Categories: <?= count($categories) ?></p>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Category
        </a>
    </div>
</div>

<?php if (count($categories) > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th width="50">ID</th>
                            <th width="60">Icon</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th width="80">Order</th>
                            <th width="100">Classes</th>
                            <th width="80">Created</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['category_id'] ?></td>
                                <td class="text-center">
                                    <i class="fas <?= sanitizeString($category['category_icon']) ?> fa-2x" 
                                       style="color: <?= sanitizeString($category['color_code']) ?>"></i>
                                </td>
                                <td>
                                    <strong><?= sanitizeString($category['category_name']) ?></strong>
                                </td>
                                <td>
                                    <small><?= truncateText($category['category_description'], 80) ?></small>
                                </td>
                                <td class="text-center">
                                    <?= $category['display_order'] ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">
                                        <?= $category['class_count'] ?> classes
                                    </span>
                                </td>
                                <td>
                                    <small><?= formatDate($category['created_at']) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $category['category_id'] ?>" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($category['class_count'] == 0): ?>
                                            <a href="delete.php?id=<?= $category['category_id'] ?>" 
                                               class="btn btn-danger" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" 
                                                    title="Cannot delete - has classes assigned"
                                                    disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
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
        <i class="fas fa-info-circle"></i> No categories found. 
        <a href="create.php">Add your first category</a>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>