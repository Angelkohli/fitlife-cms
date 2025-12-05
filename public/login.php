<?php
//  User Login (7.4)
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

initSession();

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$pdo = getDBConnection();
$error = '';

//  login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeString($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // Fetch user from database 
            $stmt = $pdo->prepare("
                SELECT *
                FROM users 
                WHERE username = :username
            ");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check if account is active
                if (!$user['is_active']) {
                    $error = 'Your account has been deactivated. Please contact support.';
                } 
                // Verify password (7.3)
                elseif (password_verify($password, $user['user_password'])) {
                    
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_role'] = $user['user_role'];
                    $_SESSION['is_admin'] = ($user['user_role'] === 'admin');
                    
                    // Update last login
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
                    $stmt->execute([':id' => $user['user_id']]);
                    
                    
                    setFlashMessage('Welcome back, ' . $user['full_name'] . '!', 'success');
                    
                    // Redirect based on role
                    if ($user['user_role'] === 'admin') {
                        header('Location: ../admin/index.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                } else {
                    $error = 'Invalid username or password';
                }
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Login error. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

$page_title = "Login";
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
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
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header text-white text-center">
                <i class="fas fa-dumbbell fa-3x mb-3"></i>
                <h2 class="mb-2">FitLife Winnipeg</h2>
                <p class="mb-0">Login Portal</p>
            </div>
            <div class="card-body p-5">
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
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username or email"
                               value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                               required 
                               autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg mb-3">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-2">Don't have an account?</p>
                    <a href="register.php" class="btn btn-success btn-block">
                        <i class="fas fa-user-plus"></i> Create Member Account
                    </a>
                </div>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Back to Homepage
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Demo Accounts Info -->
        <div class="alert alert-info mt-3 text-center">
            <small>
                <strong>Demo Accounts:</strong><br>
                Admin: <code>admin</code> / <code>password</code><br>
                Member: Register a new account or use any valid credentials
            </small>
        </div>
    </div>
</body>
</html>