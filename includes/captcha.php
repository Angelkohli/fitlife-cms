<?php
/**
 * CAPTCHA(2.10)
 */

/**
 * Validate CAPTCHA input
 * @param string $user_input User's CAPTCHA input
 * @return bool True if valid
 */
function validateCaptcha($user_input) {
   
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check CAPTCHA exists in session
    if (!isset($_SESSION['captcha_text'])) {
        return false;
    }
    
    // Check if CAPTCHA is expired (5 mins)
    if (isset($_SESSION['captcha_time'])) {
        $elapsed = time() - $_SESSION['captcha_time'];
        if ($elapsed > 300) { 
            return false;
        }
    }
    
    // Compare 
    $is_valid = (strtoupper(trim($user_input)) === strtoupper($_SESSION['captcha_text']));
    
   
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