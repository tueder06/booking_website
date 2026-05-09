<?php
session_start();

$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_code = '';
for ($i = 0; $i < 6; $i++) {
    $captcha_code .= $chars[rand(0, strlen($chars) - 1)];
}

$_SESSION['captcha_code'] = $captcha_code;

$width = 200;
$height = 60;
$image = imagecreatetruecolor($width, $height);

$white = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $white);

$font_path = __DIR__ . '/../fonts/roboto.ttf';

if (file_exists($font_path)) {
    $x_spacing = $width / 7;
    
    for ($i = 0; $i < strlen($captcha_code); $i++) {
        $text_color = imagecolorallocate($image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        
        $angle = mt_rand(-30, 30);
        
        $y = mt_rand(35, 45);
        $x = ($i * $x_spacing) + 15;
        
        imagettftext($image, 24, $angle, $x, $y, $text_color, $font_path, $captcha_code[$i]);
    }
} else {
    imagestring($image, 5, 60, 20, $captcha_code, imagecolorallocate($image, 0, 0, 0));
}

for($i = 0; $i < 7; $i++) {
    $line_color = imagecolorallocate($image, mt_rand(100, 200), mt_rand(100, 200), mt_rand(100, 200));
    imagesetthickness($image, mt_rand(1, 2));
    imageline($image, 0, mt_rand(0, $height), $width, mt_rand(0, $height), $line_color);
}

for ($i = 0; $i < 50; $i++) {
    $dot_color = imagecolorallocate($image, mt_rand(50, 150), mt_rand(50, 150), mt_rand(50, 150));
    imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), $dot_color);
}

header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>