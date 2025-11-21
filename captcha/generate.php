<?php
/**
 * CAPTCHA Image Generator (Feature 2.10 - 5 marks)
 * Generates a random CAPTCHA image with text
 */

session_start();

// Generate random CAPTCHA text (6 characters)
$captcha_text = '';
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Avoid confusing chars (I, O, 1, 0)
$length = 6;

for ($i = 0; $i < $length; $i++) {
    $captcha_text .= $characters[rand(0, strlen($characters) - 1)];
}

// Store CAPTCHA in session
$_SESSION['captcha_text'] = $captcha_text;
$_SESSION['captcha_time'] = time();

// Create image
$width = 200;
$height = 60;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 255, 255, 255); // White background
$text_color = imagecolorallocate($image, 0, 0, 0); // Black text
$line_color = imagecolorallocate($image, 64, 64, 64); // Gray lines
$dot_color = imagecolorallocate($image, 100, 100, 100); // Gray dots

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Add random lines for noise
for ($i = 0; $i < 6; $i++) {
    imageline($image, 
        rand(0, $width), rand(0, $height),
        rand(0, $width), rand(0, $height),
        $line_color
    );
}

// Add random dots for noise
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $dot_color);
}

// Add text with varying positions and angles
$font_size = 20;
$x = 15;

for ($i = 0; $i < strlen($captcha_text); $i++) {
    $angle = rand(-15, 15); // Random angle
    $y = rand(35, 45); // Random Y position
    
    // Use built-in font (font 5 is largest)
    imagechar($image, 5, $x, $y, $captcha_text[$i], $text_color);
    
    $x += 28; // Move to next character position
}

// Output image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>