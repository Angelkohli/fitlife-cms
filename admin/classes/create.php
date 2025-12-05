<?php
//  Creating New Class(2.1)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';
require_once '../../includes/image_upload.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../public/login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Add New Class";
$is_admin = true;
$css_path = '../../assets/css/style.css';

$errors = [];
$form_data = [];

// Fetching categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order, category_name");
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all input data (4.3)
    $form_data = [
        'class_name' => sanitizeString($_POST['class_name'] ?? ''),
        'class_description' => sanitizeString($_POST['class_description'] ?? ''),
        'instructor_name' => sanitizeString($_POST['instructor_name'] ?? ''),
        'category_id' => !empty($_POST['category_id']) ? sanitizeID($_POST['category_id']) : null,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];
    
    // Validate form data (4.1)
    $errors = validateClassData($form_data);
    
    // Handle image upload (6.1)
    $uploaded_image = null;
    if (isset($_FILES['instructor_image']) && $_FILES['instructor_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadInstructorImage($_FILES['instructor_image']);
        if ($upload_result['success']) {
            $uploaded_image = $upload_result['filename'];
        } else {
            $errors[] = 'Image upload error: ' . $upload_result['error'];
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            // Generate slug
            $slug = generateSlug($form_data['class_name']);
            
            $sql = "INSERT INTO classes (
                class_name, class_description, instructor_name,
                category_id, is_active, is_featured, instructor_image_path, slug
            ) VALUES (
                :class_name, :class_description, :instructor_name,
                :category_id, :is_active, :is_featured, :instructor_image_path, :slug
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':class_name' => $form_data['class_name'],
                ':class_description' => $form_data['class_description'],
                ':instructor_name' => $form_data['instructor_name'],
                ':category_id' => $form_data['category_id'],
                ':is_active' => $form_data['is_active'],
                ':is_featured' => $form_data['is_featured'],
                ':instructor_image_path' => $uploaded_image,
                ':slug' => $slug
            ]);
            
            setFlashMessage('Class created successfully!', 'success');
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
        <h1 class="mb-4"><i class="fas fa-plus-circle"></i> Add New Class</h1>
        
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
                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- Basic Information -->
                    <h4 class="border-bottom pb-2 mb-3">Basic Information</h4>
                    
                    <div class="form-group">
                        <label for="class_name">Class Name *</label>
                        <input type="text" 
                               class="form-control" 
                               id="class_name" 
                               name="class_name"
                               value="<?= $form_data['class_name'] ?? '' ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_description">Class Description *</label>
                        <textarea class="form-control" 
                                  id="class_description" 
                                  name="class_description"
                                  rows="5"
                                  required><?= $form_data['class_description'] ?? '' ?></textarea>
                        <small class="form-text text-muted">Minimum 20 characters</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="instructor_name">Instructor Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="instructor_name" 
                                       name="instructor_name"
                                       value="<?= $form_data['instructor_name'] ?? '' ?>"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['category_id'] ?>"
                                                <?= isset($form_data['category_id']) && $form_data['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                                            <?= sanitizeString($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Upload (6.1) -->
                    <div class="form-group">
                        <label for="instructor_image">Instructor Photo (Optional)</label>
                        <input type="file" 
                               class="form-control-file" 
                               id="instructor_image" 
                               name="instructor_image"
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="form-text text-muted">
                            Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP
                        </small>
                        <div id="image-preview" class="mt-2" style="display: none;">
                            <img src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>

                    <!-- Status Options -->
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Status</h4>
                    
                    <div class="form-check mb-2">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active"
                               <?= isset($form_data['is_active']) && $form_data['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Active (visible to public)
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_featured" 
                               name="is_featured"
                               <?= isset($form_data['is_featured']) && $form_data['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">
                            Featured (highlight on homepage)
                        </label>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="border-top pt-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Class
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
// Image preview
document.getElementById('instructor_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            const img = preview.querySelector('img');
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>


<?php include '../../includes/footer.php'; ?>