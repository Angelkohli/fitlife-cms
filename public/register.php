<?php
//  User Registration (7.5)
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

initSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();
$errors = [];
$form_data = [];
$success = false;

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $form_data = [
        'username' => sanitizeString($_POST['username'] ?? ''),
        'email' => sanitizeString($_POST['email'] ?? ''),
        'full_name' => sanitizeString($_POST['full_name'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
        'membership_type' => sanitizeString($_POST['membership_type'] ?? 'Basic'),
        'fitness_goals' => sanitizeString($_POST['fitness_goals'] ?? '')
    ];
    
    // Validation
    if (empty($form_data['username']) || strlen($form_data['username']) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required";
    }
    
    if (empty($form_data['full_name']) || strlen($form_data['full_name']) < 2) {
        $errors[] = "Full name is required";
    }
    
    if (empty($form_data['password']) || strlen($form_data['password']) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check if passwords match (7.5)
    if ($form_data['password'] !== $form_data['password_confirm']) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $form_data['username']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username already taken";
        }
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $form_data['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email address already registered";
        }
    }
    
    // If no errors, create account
    if (empty($errors)) {
        try {
            // Hash password (7.3)
            $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (
                username, user_password, full_name, email, 
                user_role, membership_type, fitness_goals, 
                membership_start_date, is_active
            ) VALUES (
                :username, :password, :full_name, :email,
                'member', :membership_type, :fitness_goals,
                CURDATE(), 1
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $form_data['username'],
                ':password' => $hashed_password,
                ':full_name' => $form_data['full_name'],
                ':email' => $form_data['email'],
                ':membership_type' => $form_data['membership_type'],
                ':fitness_goals' => $form_data['fitness_goals']
            ]);
            
            $success = true;
            
        } catch (PDOException $e) {
            $errors[] = "Registration error. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

$page_title = "Register";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - FitLife Winnipeg</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .register-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0 !important;
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card register-card">
            <div class="card-header text-white text-center">
                <i class="fas fa-user-plus fa-3x mb-3"></i>
                <h2 class="mb-2">Join FitLife Winnipeg</h2>
                <p class="mb-0">Create your member account</p>
            </div>
            <div class="card-body p-5">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Registration Successful!</h5>
                        <p class="mb-0">Your account has been created. You can now login.</p>
                    </div>
                    <a href="login.php" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                <?php else: ?>
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
                    
                    <form method="POST" action="" id="registration-form">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="full_name" 
                                   name="full_name"
                                   value="<?= $form_data['full_name'] ?? '' ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username"
                                   value="<?= $form_data['username'] ?? '' ?>"
                                   required>
                            <small class="form-text text-muted">Minimum 3 characters</small>
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
                        
                        <div class="form-group">
                            <label for="password_confirm">Confirm Password *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirm" 
                                   name="password_confirm"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="membership_type">Membership Type</label>
                            <select class="form-control" id="membership_type" name="membership_type">
                                <option value="Basic" <?= ($form_data['membership_type'] ?? '') === 'Basic' ? 'selected' : '' ?>>Basic</option>
                                <option value="Premium" <?= ($form_data['membership_type'] ?? '') === 'Premium' ? 'selected' : '' ?>>Premium</option>
                                <option value="Platinum" <?= ($form_data['membership_type'] ?? '') === 'Platinum' ? 'selected' : '' ?>>Platinum</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="fitness_goals">Fitness Goals (Optional)</label>
                            <textarea class="form-control" 
                                      id="fitness_goals" 
                                      name="fitness_goals"
                                      rows="3"
                                      placeholder="What are your fitness goals?"><?= $form_data['fitness_goals'] ?? '' ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>
                    
                    <hr>
                    
                    <div class="text-center">
                        <p class="mb-2">Already have an account?</p>
                        <a href="login.php" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Back to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Password match validation
    document.getElementById('registration-form')?.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            document.getElementById('password_confirm').focus();
        }
    });
    </script>
</body>
</html>