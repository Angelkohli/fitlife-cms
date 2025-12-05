<?php
//User Management (Feature 7.2)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
requireAdmin();

$pdo = getDBConnection();
$page_title = "Manage Users";
$is_admin = true;
$css_path = '../../assets/css/style.css';

// Fetch all users
$stmt = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();

// Get stats
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'admin'");
$admin_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'member'");
$member_count = $stmt->fetchColumn();

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="fas fa-users"></i> Manage Users</h1>
        <p class="text-muted">Total Users: <?= count($users) ?></p>
    </div>
    <div class="col-md-4 text-right">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add New User
        </a>
    </div>
</div>

<!-- Stats-->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Administrators</h6>
                        <h2 class="mb-0"><?= $admin_count ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-user-shield fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Members</h6>
                        <h2 class="mb-0"><?= $member_count ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<?php if (count($users) > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Membership</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['user_id'] ?></td>
                                <td><strong><?= sanitizeString($user['username']) ?></strong></td>
                                <td><?= sanitizeString($user['full_name']) ?></td>
                                <td><?= sanitizeString($user['email']) ?></td>
                                <td>
                                    <?php if ($user['user_role'] === 'admin'): ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-user-shield"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-user"></i> Member
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['membership_type']): ?>
                                        <small><?= sanitizeString($user['membership_type']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= formatDate($user['created_at']) ?></small></td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <small><?= formatDateTime($user['last_login']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Never</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $user['user_id'] ?>" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <a href="delete.php?id=<?= $user['user_id'] ?>" 
                                               class="btn btn-danger" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" 
                                                    title="Cannot delete yourself"
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
        <i class="fas fa-info-circle"></i> No users found.
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>