<?php
// Creating User (7.2)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
requireAdmin();

$pdo = getDBConnection();
$page_title = "Add New User";
$is_admin = true;
$css_path = '../../assets/css/style.css';

$errors = [];
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'username' => sanitizeString($_POST['username'] ?? ''),
        'email' => sanitizeString($_POST['email'] ?? ''),
        'full_name' => sanitizeString($_POST['full_name'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'user_role' => sanitizeString($_POST['user_role'] ?? 'member'),
        'membership_type' => sanitizeString($_POST['membership_type'] ?? ''),
        'fitness_goals' => sanitizeString($_POST['fitness_goals'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Validate
    if (empty($form_data['username']) || strlen($form_data['username']) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required";
    }
    
    if (empty($form_data['full_name'])) {
        $errors[] = "Full name is required";
    }
    
    if (empty($form_data['password']) || strlen($form_data['password']) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check duplicates
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $form_data['username']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username already exists";
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $form_data['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // Create user
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (
                username, user_password, full_name, email, user_role,
                membership_type, fitness_goals, membership_start_date, is_active
            ) VALUES (
                :username, :password, :full_name, :email, :user_role,
                :membership_type, :fitness_goals, CURDATE(), :is_active
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $form_data['username'],
                ':password' => $hashed_password,
                ':full_name' => $form_data['full_name'],
                ':email' => $form_data['email'],
                ':user_role' => $form_data['user_role'],
                ':membership_type' => $form_data['membership_type'],
                ':fitness_goals' => $form_data['fitness_goals'],
                ':is_active' => $form_data['is_active']
            ]);
            
            setFlashMessage('User created successfully!', 'success');
            header('Location: index.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="fas fa-user-plus"></i> Add New User</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <h4 class="border-bottom pb-2 mb-3">Account Information</h4>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username"
                               value="<?= $form_data['username'] ?? '' ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email"
                               value="<?= $form_data['email'] ?? '' ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password"
                               required>
                        <small class="form-text text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Personal Information</h4>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" 
                               class="form-control" 
                               id="full_name" 
                               name="full_name"
                               value="<?= $form_data['full_name'] ?? '' ?>"
                               required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_role">User Role *</label>
                                <select class="form-control" id="user_role" name="user_role" required>
                                    <option value="member" <?= ($form_data['user_role'] ?? 'member') === 'member' ? 'selected' : '' ?>>Member</option>
                                    <option value="admin" <?= ($form_data['user_role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="membership_type">Membership Type</label>
                                <select class="form-control" id="membership_type" name="membership_type">
                                    <option value="">-- Select --</option>
                                    <option value="Basic" <?= ($form_data['membership_type'] ?? '') === 'Basic' ? 'selected' : '' ?>>Basic</option>
                                    <option value="Premium" <?= ($form_data['membership_type'] ?? '') === 'Premium' ? 'selected' : '' ?>>Premium</option>
                                    <option value="Platinum" <?= ($form_data['membership_type'] ?? '') === 'Platinum' ? 'selected' : '' ?>>Platinum</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fitness_goals">Fitness Goals</label>
                        <textarea class="form-control" 
                                  id="fitness_goals" 
                                  name="fitness_goals"
                                  rows="3"><?= $form_data['fitness_goals'] ?? '' ?></textarea>
                    </div>
                    
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Status</h4>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active"
                               checked>
                        <label class="form-check-label" for="is_active">
                            Active (user can login)
                        </label>
                    </div>
                    
                    <div class="border-top pt-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create User
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>