<?php
/**
 * Image Upload Handler (Feature 6.1 - 5 marks)
 * Handles instructor image uploads with validation
 */

/**
 * Check if GD extension is available for image processing
 */
function isGDAvailable() {
    return extension_loaded('gd') && function_exists('imagecreatefromjpeg');
}

/**
 * Upload instructor image
 * @param array $file $_FILES array element
 * @param string $upload_dir Upload directory path
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadInstructorImage($file, $upload_dir = '../uploads/instructors/') {
    $result = [
        'success' => false,
        'filename' => null,
        'error' => ''
    ];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $result['error'] = 'No file uploaded';
        return $result;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Upload error: ' . $file['error'];
        return $result;
    }
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $max_size) {
        $result['error'] = 'File too large. Maximum size is 5MB';
        return $result;
    }
    
    // Check if file is an actual image (Feature 6.1 requirement)
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        $result['error'] = 'File is not a valid image';
        return $result;
    }
    
    // Allowed image types
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($check['mime'], $allowed_types)) {
        $result['error'] = 'Invalid image type. Allowed: JPG, PNG, GIF, WebP';
        return $result;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'instructor_' . uniqid() . '_' . time() . '.' . strtolower($extension);
    $target_path = $upload_dir . $filename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Auto-resize image only if GD is available (Feature 6.3 - 5 marks)
        if (isGDAvailable()) {
            resizeImage($target_path, 800, 600);
        } else {
            error_log("GD extension not available - image uploaded but not resized");
            // You might want to show a warning to the admin
        }
        
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'Failed to move uploaded file';
    }
    
    return $result;
}

/**
 * Resize image to maximum dimensions (Feature 6.3)
 * @param string $file_path Path to image file
 * @param int $max_width Maximum width
 * @param int $max_height Maximum height
 * @return bool Success status
 */
function resizeImage($file_path, $max_width = 800, $max_height = 600) {
    // Check if GD is available
    if (!isGDAvailable()) {
        error_log("GD extension not available - cannot resize image");
        return false;
    }
    
    // Get image info
    $image_info = getimagesize($file_path);
    if ($image_info === false) {
        return false;
    }
    
    list($orig_width, $orig_height, $image_type) = $image_info;
    
    // Check if resize is needed
    if ($orig_width <= $max_width && $orig_height <= $max_height) {
        return true; // No resize needed
    }
    
    // Calculate new dimensions
    $ratio = min($max_width / $orig_width, $max_height / $orig_height);
    $new_width = round($orig_width * $ratio);
    $new_height = round($orig_height * $ratio);
    
    // Create image resource from file
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file_path);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($file_path);
            break;
        default:
            return false;
    }
    
    if ($source === false) {
        return false;
    }
    
    // Create new image
    $resized = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Resize
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    
    // Save resized image
    $result = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($resized, $file_path, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($resized, $file_path, 9);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($resized, $file_path);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($resized, $file_path, 90);
            break;
    }
    
    // Free memory
    imagedestroy($source);
    imagedestroy($resized);
    
    return $result;
}

// ... rest of your existing functions remain the same ...

/**
 * Delete instructor image
 * @param string $filename Image filename
 * @param string $upload_dir Upload directory path
 * @return bool Success status
 */
function deleteInstructorImage($filename, $upload_dir = '../uploads/instructors/') {
    if (empty($filename)) {
        return false;
    }
    
    $file_path = $upload_dir . $filename;
    
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    
    return false;
}

/**
 * Validate image file before upload
 * @param array $file $_FILES array element
 * @return array ['valid' => bool, 'error' => string]
 */
function validateImageFile($file) {
    $result = ['valid' => false, 'error' => ''];
    
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $result; // No file is ok (optional upload)
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Upload error occurred';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > 5 * 1024 * 1024) {
        $result['error'] = 'Image must be less than 5MB';
        return $result;
    }
    
    // Check if it's an image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        $result['error'] = 'File must be an image (JPG, PNG, GIF)';
        return $result;
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Get image dimensions
 * @param string $file_path Path to image file
 * @return array|false ['width' => int, 'height' => int, 'type' => string]
 */
function getImageInfo($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $info = getimagesize($file_path);
    if ($info === false) {
        return false;
    }
    
    return [
        'width' => $info[0],
        'height' => $info[1],
        'type' => $info['mime']
    ];
}
?>