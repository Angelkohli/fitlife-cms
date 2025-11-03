<?php
// Simple Admin Login (Will be replaced with proper auth in Week 14)
require_once '../includes/functions.php';

initSession();

$error = '';

// If already logged in, redirect to dashboard
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Simple temporary authentication (WILL BE IMPROVED IN WEEK 14)
    // Default credentials: admin / admin123
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['is_admin'] = true;
        $_SESSION['username'] = 'admin';
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'admin';
        $_SESSION['full_name'] = 'System Administrator';
        
        setFlashMessage('Welcome back, Admin!', 'success');
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

$page_title = "Admin Login";
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-dumbbell fa-3x text-primary mb-3"></i>
                    <h2 class="mb-2">FitLife Winnipeg</h2>
                    <p class="text-muted">Admin Login</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            required 
                            autofocus
                            placeholder="Enter username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="Enter password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <a href="../public/index.php" class="text-muted small">
                        <i class="fas fa-arrow-left"></i> Back to Public Site
                    </a>
                </div>
                
                <div class="alert alert-info mt-3 small">
                    <strong>Week 11 Temporary Login:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>admin123</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>