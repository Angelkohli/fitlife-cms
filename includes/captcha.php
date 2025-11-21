<?php
/**
 * CAPTCHA Validation Functions (Feature 2.10)
 */

/**
 * Validate CAPTCHA input
 * @param string $user_input User's CAPTCHA input
 * @return bool True if valid
 */
function validateCaptcha($user_input) {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if CAPTCHA exists in session
    if (!isset($_SESSION['captcha_text'])) {
        return false;
    }
    
    // Check if CAPTCHA is expired (5 minutes)
    if (isset($_SESSION['captcha_time'])) {
        $elapsed = time() - $_SESSION['captcha_time'];
        if ($elapsed > 300) { // 5 minutes
            return false;
        }
    }
    
    // Compare (case-insensitive)
    $is_valid = (strtoupper(trim($user_input)) === strtoupper($_SESSION['captcha_text']));
    
    // Clear CAPTCHA from session after validation attempt
    if ($is_valid) {
        unset($_SESSION['captcha_text']);
        unset($_SESSION['captcha_time']);
    }
    
    return $is_valid;
}

/**
 * Clear CAPTCHA from session
 */
function clearCaptcha() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['captcha_text']);
    unset($_SESSION['captcha_time']);
}

/**
 * Check if CAPTCHA needs refresh
 * @return bool True if expired or missing
 */
function captchaExpired() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['captcha_text']) || !isset($_SESSION['captcha_time'])) {
        return true;
    }
    
    $elapsed = time() - $_SESSION['captcha_time'];
    return ($elapsed > 300); // 5 minutes
}
?>