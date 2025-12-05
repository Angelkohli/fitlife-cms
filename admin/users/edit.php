<?php
//Edit User (7.2)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
requireAdmin();

$pdo = getDBConnection();
$page_title = "Edit User";
$is_admin = true;
$css_path = '../../assets/css/style.css';

$errors = [];
$user_id = sanitizeID($_GET['id'] ?? 0);

if (!$user_id) {
    setFlashMessage('Invalid user ID', 'error');
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
    
    // Check duplicates
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND user_id != :id");
        $stmt->execute([':username' => $form_data['username'], ':id' => $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username already exists";
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND user_id != :id");
        $stmt->execute([':email' => $form_data['email'], ':id' => $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // Update user
    if (empty($errors)) {
        try {
            
            if (!empty($form_data['password'])) {
                $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET
                    username = :username,
                    user_password = :password,
                    full_name = :full_name,
                    email = :email,
                    user_role = :user_role,
                    membership_type = :membership_type,
                    fitness_goals = :fitness_goals,
                    is_active = :is_active
                    WHERE user_id = :user_id";
                
                $params = [
                    ':username' => $form_data['username'],
                    ':password' => $hashed_password,
                    ':full_name' => $form_data['full_name'],
                    ':email' => $form_data['email'],
                    ':user_role' => $form_data['user_role'],
                    ':membership_type' => $form_data['membership_type'],
                    ':fitness_goals' => $form_data['fitness_goals'],
                    ':is_active' => $form_data['is_active'],
                    ':user_id' => $user_id
                ];
            } else {
                $sql = "UPDATE users SET
                    username = :username,
                    full_name = :full_name,
                    email = :email,
                    user_role = :user_role,
                    membership_type = :membership_type,
                    fitness_goals = :fitness_goals,
                    is_active = :is_active
                    WHERE user_id = :user_id";
                
                $params = [
                    ':username' => $form_data['username'],
                    ':full_name' => $form_data['full_name'],
                    ':email' => $form_data['email'],
                    ':user_role' => $form_data['user_role'],
                    ':membership_type' => $form_data['membership_type'],
                    ':fitness_goals' => $form_data['fitness_goals'],
                    ':is_active' => $form_data['is_active'],
                    ':user_id' => $user_id
                ];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            setFlashMessage('User updated successfully!', 'success');
            header('Location: index.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else {
        $user = array_merge($user, $form_data);
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="fas fa-user-edit"></i> Edit User</h1>
        
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
                               value="<?= sanitizeString($user['username']) ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email"
                               value="<?= sanitizeString($user['email']) ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password">
                        <small class="form-text text-muted">Leave blank to keep current password</small>
                    </div>
                    
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Personal Information</h4>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" 
                               class="form-control" 
                               id="full_name" 
                               name="full_name"
                               value="<?= sanitizeString($user['full_name']) ?>"
                               required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_role">User Role *</label>
                                <select class="form-control" id="user_role" name="user_role" required>
                                    <option value="member" <?= $user['user_role'] === 'member' ? 'selected' : '' ?>>Member</option>
                                    <option value="admin" <?= $user['user_role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="membership_type">Membership Type</label>
                                <select class="form-control" id="membership_type" name="membership_type">
                                    <option value="">-- Select --</option>
                                    <option value="Basic" <?= $user['membership_type'] === 'Basic' ? 'selected' : '' ?>>Basic</option>
                                    <option value="Premium" <?= $user['membership_type'] === 'Premium' ? 'selected' : '' ?>>Premium</option>
                                    <option value="Platinum" <?= $user['membership_type'] === 'Platinum' ? 'selected' : '' ?>>Platinum</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fitness_goals">Fitness Goals</label>
                        <textarea class="form-control" 
                                  id="fitness_goals" 
                                  name="fitness_goals"
                                  rows="3"><?= sanitizeString($user['fitness_goals']) ?></textarea>
                    </div>
                    
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Account Details</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Account Created:</strong><br>
                            <?= formatDateTime($user['created_at']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Last Login:</strong><br>
                            <?= $user['last_login'] ? formatDateTime($user['last_login']) : 'Never' ?>
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active"
                               <?= $user['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Active (user can login)
                        </label>
                    </div>
                    
                    <div class="border-top pt-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <?php if ($user_id != $_SESSION['user_id']): ?>
                            <a href="delete.php?id=<?= $user_id ?>" 
                               class="btn btn-danger float-right"
                               onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="fas fa-trash"></i> Delete User
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>