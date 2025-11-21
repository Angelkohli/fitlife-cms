<?php
// Admin - Delete User (Feature 7.2)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
requireAdmin();

$pdo = getDBConnection();
$user_id = sanitizeID($_GET['id'] ?? 0);

if (!$user_id) {
    setFlashMessage('Invalid user ID', 'error');
    header('Location: index.php');
    exit;
}

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    setFlashMessage('You cannot delete your own account', 'error');
    header('Location: index.php');
    exit;
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('User not found', 'error');
    header('Location: index.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete user (CASCADE will handle reviews if user_id is set in reviews)
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $user_id]);
        
        setFlashMessage('User "' . $user['username'] . '" has been deleted', 'success');
        header('Location: index.php');
        exit;
        
    } catch (PDOException $e) {
        setFlashMessage('Error deleting user: ' . $e->getMessage(), 'error');
    }
}

$page_title = "Delete User";
$is_admin = true;
$css_path = '../../assets/css/style.css';

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Confirm User Deletion</h4>
            </div>
            <div class="card-body">
                <p class="lead">Are you sure you want to delete this user?</p>
                
                <div class="alert alert-warning">
                    <strong>Username:</strong> <?= sanitizeString($user['username']) ?><br>
                    <strong>Full Name:</strong> <?= sanitizeString($user['full_name']) ?><br>
                    <strong>Email:</strong> <?= sanitizeString($user['email']) ?><br>
                    <strong>Role:</strong> <?= ucfirst($user['user_role']) ?>
                </div>
                
                <p class="text-danger mb-4">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Warning:</strong> This action cannot be undone. 
                    Any reviews submitted by this user will have their user_id set to NULL.
                </p>
                
                <form method="POST" action="">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete This User
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