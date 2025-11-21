<?php
// Admin - Delete Class (Feature 2.2 - Part of 5 marks)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../public/login.php');
    exit;
}

$pdo = getDBConnection();

// Sanitize and validate the ID (Feature 4.2)
$class_id = sanitizeID($_GET['id'] ?? 0);

if (!$class_id) {
    setFlashMessage('Invalid class ID', 'error');
    header('Location: index.php');
    exit;
}

// Fetch the class to confirm it exists
$stmt = $pdo->prepare("SELECT class_name FROM classes WHERE class_id = :class_id");
$stmt->execute([':class_id' => $class_id]);
$class = $stmt->fetch();

if (!$class) {
    setFlashMessage('Class not found', 'error');
    header('Location: index.php');
    exit;
}

// Handle the deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete the class (CASCADE will handle related reviews)
        $stmt = $pdo->prepare("DELETE FROM classes WHERE class_id = :class_id");
        $stmt->execute([':class_id' => $class_id]);
        
        setFlashMessage('Class "' . $class['class_name'] . '" has been deleted successfully.', 'success');
        header('Location: index.php');
        exit;
        
    } catch (PDOException $e) {
        setFlashMessage('Error deleting class: ' . $e->getMessage(), 'error');
    }
}

$page_title = "Delete Class";
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
                <p class="lead">Are you sure you want to delete this class?</p>
                
                <div class="alert alert-warning">
                    <strong>Class Name:</strong> <?= sanitizeString($class['class_name']) ?>
                </div>
                
                <p class="text-danger mb-4">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Warning:</strong> This action cannot be undone. All associated reviews will also be deleted.
                </p>
                
                <form method="POST" action="">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete This Class
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