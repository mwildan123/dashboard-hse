<?php
$src = imagecreatefrompng('public/image/prysmian_transparent.png');
$width = imagesx($src);
$height = imagesy($src);
$dest = imagecreatetruecolor($width, $height);
imagealphablending($dest, false);
imagesavealpha($dest, true);
$transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
imagefill($dest, 0, 0, $transparent);

// Get background color at pixel (0,0)
$bg = imagecolorat($src, 0, 0);
$bg_r = ($bg >> 16) & 0xFF;
$bg_g = ($bg >> 8) & 0xFF;
$bg_b = $bg & 0xFF;

for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $color = imagecolorat($src, $x, $y);
        $r = ($color >> 16) & 0xFF;
        $g = ($color >> 8) & 0xFF;
        $b = $color & 0xFF;
        
        $dist = sqrt(pow($r-$bg_r,2) + pow($g-$bg_g,2) + pow($b-$bg_b,2));
        // Remove pixels similar to the background
        if ($dist < 30) {
            imagesetpixel($dest, $x, $y, $transparent);
        } else {
            // Check edges/anti-aliasing (semi-transparent)
            if ($dist >= 30 && $dist < 90) {
                $alpha = (int)(127 - (127 * ($dist - 30) / 60));
                imagesetpixel($dest, $x, $y, imagecolorallocatealpha($dest, $r, $g, $b, $alpha));
            } else {
                imagesetpixel($dest, $x, $y, imagecolorallocatealpha($dest, $r, $g, $b, 0));
            }
        }
    }
}
imagepng($dest, 'public/image/prysmian_transparent_clean.png');
echo 'Done';
