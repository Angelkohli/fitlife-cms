<?php
/**
 * Validation and Sanitization Functions
 * Feature 4.1, 4.2, 4.3 - Security & Validation
 */

/**
 * Sanitize and validate an ID (Feature 4.2)
 */
function sanitizeID($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    return ($id !== false && $id > 0) ? $id : false;
}

/**
 * Sanitize string to prevent XSS attacks (Feature 4.3)
 */
function sanitizeString($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize HTML content (for WYSIWYG editors)
 */
function sanitizeHTML($html) {
    // Allow specific HTML tags for WYSIWYG content
    $allowed_tags = '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img>';
    return strip_tags(trim($html), $allowed_tags);
}

/**
 * Validate email address
 */
function validateEmail($email) {
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    return $email !== false ? $email : false;
}

/**
 * Validate class data (Feature 4.1)
 */
function validateClassData($data) {
    $errors = [];
    
    // Class name validation
    if (empty($data['class_name']) || strlen(trim($data['class_name'])) < 3) {
        $errors[] = "Class name is required (minimum 3 characters)";
    }
    
    // Description validation
    if (empty($data['class_description']) || strlen(trim($data['class_description'])) < 20) {
        $errors[] = "Class description is required (minimum 20 characters)";
    }
    
    // Instructor name validation
    if (empty($data['instructor_name']) || strlen(trim($data['instructor_name'])) < 2) {
        $errors[] = "Instructor name is required";
    }
    
    
    // Category validation (if provided)
    if (!empty($data['category_id'])) {
        $category_id = sanitizeID($data['category_id']);
        if ($category_id === false) {
            $errors[] = "Invalid category selected";
        }
    }
    
    return $errors;
}

/**
 * Validate user registration data
 */
function validateUserData($data) {
    $errors = [];
    
    // Username validation
    if (empty($data['username']) || strlen(trim($data['username'])) < 3) {
        $errors[] = "Username is required (minimum 3 characters)";
    }
    
    // Email validation
    if (empty($data['email']) || !validateEmail($data['email'])) {
        $errors[] = "Valid email address is required";
    }
    
    // Password validation
    if (empty($data['user_password']) || strlen($data['user_password']) < 6) {
        $errors[] = "Password is required (minimum 6 characters)";
    }
    
    // Full name validation
    if (empty($data['full_name']) || strlen(trim($data['full_name'])) < 2) {
        $errors[] = "Full name is required";
    }
    
    return $errors;
}

/**
 * Validate comment/review data
 */
function validateCommentData($data) {
    $errors = [];
    
    // Member name validation
    if (empty($data['member_name']) || strlen(trim($data['member_name'])) < 2) {
        $errors[] = "Name is required";
    }
    
    // Review text validation
    if (empty($data['review_text']) || strlen(trim($data['review_text'])) < 10) {
        $errors[] = "Review must be at least 10 characters";
    }
    
    // Rating validation
    if (empty($data['review_rating']) || $data['review_rating'] < 1 || $data['review_rating'] > 5) {
        $errors[] = "Rating must be between 1 and 5 stars";
    }
    
    return $errors;
}

/**
 * Generate slug from text
 */
function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}
?>