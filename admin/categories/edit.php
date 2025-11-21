<?php
// Admin - Edit Category (Feature 2.4 - Part of 5 marks)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../public/login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Edit Category";
$is_admin = true;
$css_path = '../../assets/css/style.css';

$errors = [];
$category_id = sanitizeID($_GET['id'] ?? 0);

if (!$category_id) {
    setFlashMessage('Invalid category ID', 'error');
    header('Location: index.php');
    exit;
}

// Fetch category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = :id");
$stmt->execute([':id' => $category_id]);
$category = $stmt->fetch();

if (!$category) {
    setFlashMessage('Category not found', 'error');
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    // Check for duplicate (excluding current category)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_name = :name AND category_id != :id");
    $stmt->execute([':name' => $form_data['category_name'], ':id' => $category_id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "A category with this name already exists";
    }
    
    // If no errors, update
    if (empty($errors)) {
        try {
            $sql = "UPDATE categories SET
                category_name = :category_name,
                category_description = :category_description,
                category_icon = :category_icon,
                color_code = :color_code,
                display_order = :display_order
                WHERE category_id = :category_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':category_name' => $form_data['category_name'],
                ':category_description' => $form_data['category_description'],
                ':category_icon' => $form_data['category_icon'],
                ':color_code' => $form_data['color_code'],
                ':display_order' => $form_data['display_order'],
                ':category_id' => $category_id
            ]);
            
            setFlashMessage('Category updated successfully!', 'success');
            header('Location: index.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else {
        $category = $form_data;
        $category['category_id'] = $category_id;
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="fas fa-edit"></i> Edit Category</h1>
        
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
                               value="<?= sanitizeString($category['category_name']) ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_description">Description *</label>
                        <textarea class="form-control" 
                                  id="category_description" 
                                  name="category_description"
                                  rows="3"
                                  required><?= sanitizeString($category['category_description']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_icon">Font Awesome Icon Class</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="category_icon" 
                                       name="category_icon"
                                       value="<?= sanitizeString($category['category_icon']) ?>">
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
                                       value="<?= sanitizeString($category['color_code']) ?>"
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
                               value="<?= $category['display_order'] ?>"
                               min="0">
                    </div>
                    
                    <!-- Preview -->
                    <div class="alert alert-info">
                        <strong>Preview:</strong><br>
                        <div class="mt-2 d-flex align-items-center">
                            <i class="fas <?= sanitizeString($category['category_icon']) ?> fa-3x mr-3" 
                               id="icon-preview" 
                               style="color: <?= sanitizeString($category['color_code']) ?>"></i>
                            <div>
                                <h5 class="mb-0" id="name-preview"><?= sanitizeString($category['category_name']) ?></h5>
                                <small id="desc-preview"><?= sanitizeString($category['category_description']) ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Category
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
    document.getElementById('name-preview').textContent = this.value;
});

document.getElementById('category_description').addEventListener('input', function() {
    document.getElementById('desc-preview').textContent = this.value;
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