<?php
/**
 * CAPTCHA(2.10)
 */

session_start();

// random CAPTCHA text 
$captcha_text = '';
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; 
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
    $angle = rand(-15, 15);
    $y = rand(35, 45); 
    
    // Using built-in font 
    imagechar($image, 5, $x, $y, $captcha_text[$i], $text_color);
    
    $x += 28; 
}

// Output image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>