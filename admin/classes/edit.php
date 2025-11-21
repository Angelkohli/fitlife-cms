<?php
// Admin - Edit Class (Feature 2.2 - Part of 5 marks)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';
require_once '../../includes/image_upload.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDBConnection();
$page_title = "Edit Class";
$is_admin = true;
$css_path = '../../assets/css/style.css';

$errors = [];
$class_id = sanitizeID($_GET['id'] ?? 0);

if (!$class_id) {
    setFlashMessage('Invalid class ID', 'error');
    header('Location: index.php');
    exit;
}

// Fetch the class data
$stmt = $pdo->prepare("SELECT * FROM classes WHERE class_id = :class_id");
$stmt->execute([':class_id' => $class_id]);
$class = $stmt->fetch();

if (!$class) {
    setFlashMessage('Class not found', 'error');
    header('Location: index.php');
    exit;
}

// Fetch categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order, category_name");
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all input data
    $form_data = [
        'class_name' => sanitizeString($_POST['class_name'] ?? ''),
        'class_description' => sanitizeString($_POST['class_description'] ?? ''),
        'instructor_name' => sanitizeString($_POST['instructor_name'] ?? ''),
        'category_id' => !empty($_POST['category_id']) ? sanitizeID($_POST['category_id']) : null,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];
    
    // Handle image operations (Feature 6.1 & 6.2)
    $new_image_path = $class['instructor_image_path'];

    // Delete existing image if requested (Feature 6.2)
    if (isset($_POST['delete_image']) && $class['instructor_image_path']) {
        deleteInstructorImage($class['instructor_image_path']);
        $new_image_path = null;
    }

    // Upload new image if provided
    if (isset($_FILES['instructor_image']) && $_FILES['instructor_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadInstructorImage($_FILES['instructor_image']);
        if ($upload_result['success']) {
            // Delete old image if exists (Feature 6.2)
            if ($class['instructor_image_path']) {
                deleteInstructorImage($class['instructor_image_path']);
            }
            $new_image_path = $upload_result['filename'];
        } else {
            $errors[] = 'Image upload error: ' . $upload_result['error'];
        }
    }

    // Validate form data
    $errors = validateClassData($form_data);
    
    // If no errors, update the database
    if (empty($errors)) {
        try {
            // Generate slug
            $slug = generateSlug($form_data['class_name']);
            
            $sql = "UPDATE classes SET
                class_name = :class_name,
                class_description = :class_description,
                instructor_name = :instructor_name,
                category_id = :category_id,
                is_active = :is_active,
                is_featured = :is_featured,
                slug = :slug,
                instructor_image_path = :instructor_image_path
                WHERE class_id = :class_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':class_name' => $form_data['class_name'],
                ':class_description' => $form_data['class_description'],
                ':instructor_name' => $form_data['instructor_name'],
                ':category_id' => $form_data['category_id'],
                ':is_active' => $form_data['is_active'],
                ':is_featured' => $form_data['is_featured'],
                ':slug' => $slug,
                ':instructor_image_path' => $new_image_path,
                ':class_id' => $class_id
            ]);
            
            setFlashMessage('Class updated successfully!', 'success');
            header('Location: index.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else {
        // Use submitted form data if validation failed
        $class = $form_data;
        $class['class_id'] = $class_id;
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="fas fa-edit"></i> Edit Class</h1>
        
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
                               value="<?= sanitizeString($class['class_name']) ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_description">Class Description *</label>
                        <textarea class="form-control" 
                                  id="class_description" 
                                  name="class_description"
                                  rows="5"
                                  required><?= sanitizeString($class['class_description']) ?></textarea>
                        <small class="form-text text-muted">Minimum 20 characters</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">

                            <!-- Current Image Display (Feature 6.4) -->
                            <?php if ($class['instructor_image_path']): ?>
                                <div class="form-group">
                                    <label>Current Instructor Photo</label>
                                    <div>
                                        <img src="../../uploads/instructors/<?= sanitizeString($class['instructor_image_path']) ?>" 
                                            alt="Current instructor photo" 
                                            class="img-thumbnail"
                                            style="max-width: 200px;">
                                    </div>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" id="delete_image" name="delete_image">
                                        <label class="form-check-label" for="delete_image">
                                            Delete current image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Upload New Image (Feature 6.1) -->
                            <div class="form-group">
                                <label for="instructor_image">
                                    <?= $class['instructor_image_path'] ? 'Replace' : 'Upload' ?> Instructor Photo (Optional)
                                </label>
                                <input type="file" 
                                    class="form-control-file" 
                                    id="instructor_image" 
                                    name="instructor_image"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="form-text text-muted">
                                    Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="instructor_name">Instructor Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="instructor_name" 
                                       name="instructor_name"
                                       value="<?= sanitizeString($class['instructor_name']) ?>"
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
                                                <?= $class['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                                            <?= sanitizeString($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="border-top pt-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Class
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <a href="delete.php?id=<?= $class_id ?>" 
                           class="btn btn-danger float-right"
                           onclick="return confirm('Are you sure you want to delete this class?')">
                            <i class="fas fa-trash"></i> Delete Class
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>