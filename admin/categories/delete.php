<?php
// Admin - Delete(2.4)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../public/login.php');
    exit;
}

$pdo = getDBConnection();
$category_id = sanitizeID($_GET['id'] ?? 0);

if (!$category_id) {
    setFlashMessage('Invalid category ID', 'error');
    header('Location: index.php');
    exit;
}

// Fetching category and check if it has classes
$stmt = $pdo->prepare("
    SELECT cat.*, COUNT(c.class_id) as class_count
    FROM categories cat
    LEFT JOIN classes c ON cat.category_id = c.category_id
    WHERE cat.category_id = :id
    GROUP BY cat.category_id
");
$stmt->execute([':id' => $category_id]);
$category = $stmt->fetch();

if (!$category) {
    setFlashMessage('Category not found', 'error');
    header('Location: index.php');
    exit;
}

// Preventing deletion if category has classes
if ($category['class_count'] > 0) {
    setFlashMessage('Cannot delete category: ' . $category['class_count'] . ' classes are assigned to it', 'error');
    header('Location: index.php');
    exit;
}

// Handling deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :id");
        $stmt->execute([':id' => $category_id]);
        
        setFlashMessage('Category "' . $category['category_name'] . '" deleted successfully', 'success');
        header('Location: index.php');
        exit;
        
    } catch (PDOException $e) {
        setFlashMessage('Error deleting category: ' . $e->getMessage(), 'error');
    }
}

$page_title = "Delete Category";
$is_admin = true;
$css_path = '../../assets/css/style.css';

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h4>
            </div>
            <div class="card-body">
                <p class="lead">Are you sure you want to delete this category?</p>
                
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= sanitizeString($category['category_icon']) ?> fa-3x mr-3" 
                           style="color: <?= sanitizeString($category['color_code']) ?>"></i>
                        <div>
                            <strong><?= sanitizeString($category['category_name']) ?></strong><br>
                            <small><?= sanitizeString($category['category_description']) ?></small>
                        </div>
                    </div>
                </div>
                
                <p class="text-danger mb-4">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Warning:</strong> This action cannot be undone.
                </p>
                
                <form method="POST" action="">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete This Category
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>