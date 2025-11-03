<?php
// Admin - Edit Class (Feature 2.2 - Part of 5 marks)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

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
        'duration_minutes' => sanitizeID($_POST['duration_minutes'] ?? 0),
        'difficulty_level' => sanitizeString($_POST['difficulty_level'] ?? ''),
        'max_participants' => sanitizeID($_POST['max_participants'] ?? 0),
        'current_enrolled' => sanitizeID($_POST['current_enrolled'] ?? 0),
        'day_of_week' => sanitizeString($_POST['day_of_week'] ?? ''),
        'start_time' => sanitizeString($_POST['start_time'] ?? ''),
        'class_location' => sanitizeString($_POST['class_location'] ?? ''),
        'room_number' => sanitizeString($_POST['room_number'] ?? ''),
        'equipment_needed' => sanitizeString($_POST['equipment_needed'] ?? ''),
        'calories_burned_avg' => sanitizeID($_POST['calories_burned_avg'] ?? 0),
        'category_id' => !empty($_POST['category_id']) ? sanitizeID($_POST['category_id']) : null,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];
    
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
                duration_minutes = :duration_minutes,
                difficulty_level = :difficulty_level,
                max_participants = :max_participants,
                current_enrolled = :current_enrolled,
                day_of_week = :day_of_week,
                start_time = :start_time,
                class_location = :class_location,
                room_number = :room_number,
                equipment_needed = :equipment_needed,
                calories_burned_avg = :calories_burned_avg,
                category_id = :category_id,
                is_active = :is_active,
                is_featured = :is_featured,
                slug = :slug
                WHERE class_id = :class_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':class_name' => $form_data['class_name'],
                ':class_description' => $form_data['class_description'],
                ':instructor_name' => $form_data['instructor_name'],
                ':duration_minutes' => $form_data['duration_minutes'],
                ':difficulty_level' => $form_data['difficulty_level'],
                ':max_participants' => $form_data['max_participants'],
                ':current_enrolled' => $form_data['current_enrolled'],
                ':day_of_week' => $form_data['day_of_week'],
                ':start_time' => $form_data['start_time'],
                ':class_location' => $form_data['class_location'],
                ':room_number' => $form_data['room_number'],
                ':equipment_needed' => $form_data['equipment_needed'],
                ':calories_burned_avg' => $form_data['calories_burned_avg'],
                ':category_id' => $form_data['category_id'],
                ':is_active' => $form_data['is_active'],
                ':is_featured' => $form_data['is_featured'],
                ':slug' => $slug,
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
                <form method="POST" action="">
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
                    
                    <!-- Schedule Details -->
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Schedule Details</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="day_of_week">Day of Week *</label>
                                <select class="form-control" id="day_of_week" name="day_of_week" required>
                                    <?php 
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    foreach ($days as $day): 
                                    ?>
                                        <option value="<?= $day ?>" <?= $class['day_of_week'] === $day ? 'selected' : '' ?>>
                                            <?= $day ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_time">Start Time *</label>
                                <input type="time" 
                                       class="form-control" 
                                       id="start_time" 
                                       name="start_time"
                                       value="<?= $class['start_time'] ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="duration_minutes">Duration (minutes) *</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="duration_minutes" 
                                       name="duration_minutes"
                                       value="<?= $class['duration_minutes'] ?>"
                                       min="15"
                                       max="180"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="class_location">Location *</label>
                                <select class="form-control" id="class_location" name="class_location" required>
                                    <option value="Downtown" <?= $class['class_location'] === 'Downtown' ? 'selected' : '' ?>>Downtown</option>
                                    <option value="St. Vital" <?= $class['class_location'] === 'St. Vital' ? 'selected' : '' ?>>St. Vital</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="room_number">Room Number</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="room_number" 
                                       name="room_number"
                                       value="<?= sanitizeString($class['room_number']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Details -->
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Class Details</h4>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="difficulty_level">Difficulty Level *</label>
                                <select class="form-control" id="difficulty_level" name="difficulty_level" required>
                                    <?php 
                                    $levels = ['Beginner', 'Intermediate', 'Advanced', 'All Levels'];
                                    foreach ($levels as $level): 
                                    ?>
                                        <option value="<?= $level ?>" <?= $class['difficulty_level'] === $level ? 'selected' : '' ?>>
                                            <?= $level ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="max_participants">Max Participants *</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="max_participants" 
                                       name="max_participants"
                                       value="<?= $class['max_participants'] ?>"
                                       min="1"
                                       max="100"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="current_enrolled">Current Enrolled</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="current_enrolled" 
                                       name="current_enrolled"
                                       value="<?= $class['current_enrolled'] ?>"
                                       min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="calories_burned_avg">Avg. Calories Burned</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="calories_burned_avg" 
                                       name="calories_burned_avg"
                                       value="<?= $class['calories_burned_avg'] ?>"
                                       min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equipment_needed">Equipment Needed</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="equipment_needed" 
                                       name="equipment_needed"
                                       value="<?= sanitizeString($class['equipment_needed']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Options -->
                    <h4 class="border-bottom pb-2 mb-3 mt-4">Status</h4>
                    
                    <div class="form-check mb-2">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active"
                               <?= $class['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Active (visible to public)
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_featured" 
                               name="is_featured"
                               <?= $class['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">
                            Featured (highlight on homepage)
                        </label>
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