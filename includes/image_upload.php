<?php
/**
 * Image Upload Handler (Feature 6.1 - 5 marks)
 * Handles instructor image uploads with validation
 */

/**
 * Upload instructor image
 * @param array $file $_FILES array element
 * @param string $upload_dir Upload directory path
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadInstructorImage($file, $upload_dir = '../../uploads/instructors/') {
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
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'Failed to move uploaded file';
    }
    
    return $result;
}

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