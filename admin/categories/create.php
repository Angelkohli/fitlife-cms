<?php
// Admin - Create Category (Feature 2.4 - Part of 5 marks)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Add New Category";
$is_admin = true;
$css_path = '../../assets/css/style.css';

$errors = [];
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input (Feature 4.3)
    $form_data = [
        'category_name' => sanitizeString($_POST['category_name'] ?? ''),
        'category_description' => sanitizeString($_POST['category_description'] ?? ''),
        'category_icon' => sanitizeString($_POST['category_icon'] ?? 'fa-tag'),
        'color_code' => sanitizeString($_POST['color_code'] ?? '#007bff'),
        'display_order' => sanitizeID($_POST['display_order'] ?? 0)
    ];
    
    // Validate
    if (empty($form_data['category_name']) || strlen($form_data['category_name']) < 2) {
        $errors[] = "Category name is required (minimum 2 characters)";
    }
    
    if (empty($form_data['category_description'])) {
        $errors[] = "Category description is required";
    }
    
    // Check for duplicate category name
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_name = :name");
    $stmt->execute([':name' => $form_data['category_name']]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "A category with this name already exists";
    }
    
    // If no errors, insert
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO categories (
                category_name, category_description, category_icon, 
                color_code, display_order
            ) VALUES (
                :category_name, :category_description, :category_icon,
                :color_code, :display_order
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':category_name' => $form_data['category_name'],
                ':category_description' => $form_data['category_description'],
                ':category_icon' => $form_data['category_icon'],
                ':color_code' => $form_data['color_code'],
                ':display_order' => $form_data['display_order']
            ]);
            
            setFlashMessage('Category created successfully!', 'success');
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
        <h1 class="mb-4"><i class="fas fa-plus-circle"></i> Add New Category</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h5>
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
                    <div class="form-group">
                        <label for="category_name">Category Name *</label>
                        <input type="text" 
                               class="form-control" 
                               id="category_name" 
                               name="category_name"
                               value="<?= $form_data['category_name'] ?? '' ?>"
                               placeholder="e.g., Yoga, Cardio, Strength"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_description">Description *</label>
                        <textarea class="form-control" 
                                  id="category_description" 
                                  name="category_description"
                                  rows="3"
                                  placeholder="Brief description of this category"
                                  required><?= $form_data['category_description'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_icon">Font Awesome Icon Class</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="category_icon" 
                                       name="category_icon"
                                       value="<?= $form_data['category_icon'] ?? 'fa-tag' ?>"
                                       placeholder="fa-dumbbell">
                                <small class="form-text text-muted">
                                    Browse icons at <a href="https://fontawesome.com/v5/search?m=free" target="_blank">FontAwesome</a>
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="color_code">Category Color</label>
                                <input type="color" 
                                       class="form-control" 
                                       id="color_code" 
                                       name="color_code"
                                       value="<?= $form_data['color_code'] ?? '#007bff' ?>"
                                       style="height: 45px;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" 
                               class="form-control" 
                               id="display_order" 
                               name="display_order"
                               value="<?= $form_data['display_order'] ?? 0 ?>"
                               min="0">
                        <small class="form-text text-muted">
                            Lower numbers appear first. Use 0 for default ordering.
                        </small>
                    </div>
                    
                    <!-- Preview -->
                    <div class="alert alert-info">
                        <strong>Preview:</strong><br>
                        <div class="mt-2 d-flex align-items-center">
                            <i class="fas fa-tag fa-3x mr-3" id="icon-preview" style="color: #007bff"></i>
                            <div>
                                <h5 class="mb-0" id="name-preview">Category Name</h5>
                                <small id="desc-preview">Category description will appear here</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Category
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

<script>
// Live preview
document.getElementById('category_name').addEventListener('input', function() {
    document.getElementById('name-preview').textContent = this.value || 'Category Name';
});

document.getElementById('category_description').addEventListener('input', function() {
    document.getElementById('desc-preview').textContent = this.value || 'Category description will appear here';
});

document.getElementById('category_icon').addEventListener('input', function() {
    let icon = this.value.trim();
    if (!icon.startsWith('fa-')) {
        icon = 'fa-' + icon;
    }
    document.getElementById('icon-preview').className = 'fas ' + icon + ' fa-3x mr-3';
    document.getElementById('icon-preview').style.color = document.getElementById('color_code').value;
});

document.getElementById('color_code').addEventListener('input', function() {
    document.getElementById('icon-preview').style.color = this.value;
});
</script>

<?php include '../../includes/footer.php'; ?>