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
            $resize_result = resizeImage($target_path, 800, 600);
            if (!$resize_result) {
                error_log("Image resize failed for: " . $filename);
                // Continue anyway - the upload was successful
            }
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
 */
function resizeImage($file_path, $max_width = 800, $max_height = 600) {
    // Get image info
    $image_info = getimagesize($file_path);
    if ($image_info === false) {
        error_log("Cannot get image size for: " . $file_path);
        return false;
    }
    
    list($orig_width, $orig_height, $image_type) = $image_info;
    
    // Check if resize is needed
    if ($orig_width <= $max_width && $orig_height <= $max_height) {
        return true; // No resize needed
    }
    
    // Calculate new dimensions while maintaining aspect ratio
    $ratio = $orig_width / $orig_height;
    
    if ($max_width / $max_height > $ratio) {
        $new_width = $max_height * $ratio;
        $new_height = $max_height;
    } else {
        $new_width = $max_width;
        $new_height = $max_width / $ratio;
    }
    
    $new_width = round($new_width);
    $new_height = round($new_height);
    
    // Create image resource from file based on image type
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
            if (function_exists('imagecreatefromwebp')) {
                $source = imagecreatefromwebp($file_path);
            } else {
                error_log("WebP not supported in this GD installation");
                return false;
            }
            break;
        default:
            error_log("Unsupported image type: " . $image_type);
            return false;
    }
    
    if (!$source) {
        error_log("Failed to create image resource from: " . $file_path);
        return false;
    }
    
    // Create new image resource for resized version
    $resized = imagecreatetruecolor($new_width, $new_height);
    if (!$resized) {
        error_log("Failed to create true color image");
        imagedestroy($source);
        return false;
    }
    
    // Preserve transparency for PNG and GIF
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        
        // For GIF, handle transparency
        if ($image_type == IMAGETYPE_GIF) {
            $transparent_index = imagecolortransparent($source);
            if ($transparent_index >= 0) {
                $transparent_color = imagecolorsforindex($source, $transparent_index);
                $transparent_index = imagecolorallocatealpha($resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue'], 127);
                imagefill($resized, 0, 0, $transparent_index);
                imagecolortransparent($resized, $transparent_index);
            }
        }
    }
    
    // Resize the image with better quality
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    
    // Save resized image back to file
    $success = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($resized, $file_path, 85); // 85% quality
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($resized, $file_path, 8); // Compression level 8 (0-9)
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($resized, $file_path);
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagewebp')) {
                $success = imagewebp($resized, $file_path, 85); // 85% quality
            } else {
                $success = false;
            }
            break;
    }
    
    // Free memory
    imagedestroy($source);
    imagedestroy($resized);
    
    if (!$success) {
        error_log("Failed to save resized image: " . $file_path);
        return false;
    }
    
    return true;
}

/**
 * Delete instructor image
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