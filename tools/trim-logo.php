<?php

/**
 * Auto-trims the transparent/near-white margin around a logo PNG.
 *
 * Uploaded logo marks are often exported on a large square canvas with a lot of
 * empty padding around the actual artwork (common from design tools' default
 * export presets). Displayed at a fixed CSS height, that padding eats most of
 * the box, so the visible mark renders far smaller than the container - simply
 * growing the container doesn't fix it, the source image needs cropping to its
 * real content bounds.
 *
 * This script scans for the bounding box of non-background pixels (alpha > 0
 * for a transparent PNG, or non-near-white for a flattened one), crops to that
 * box plus a small padding margin, and overwrites the file - after first saving
 * a .bak copy alongside it so the crop is reversible.
 *
 * Usage: php tools/trim-logo.php <path-to-png-relative-to-public> [paddingPercent]
 *   Example: php tools/trim-logo.php uploads/logos/e2a72d8824e8ebc8.png 5
 */

$relPath = $argv[1] ?? null;
$paddingPercent = isset($argv[2]) ? (float) $argv[2] : 5.0;

if ($relPath === null) {
    fwrite(STDERR, "Usage: php tools/trim-logo.php <path-relative-to-public> [paddingPercent]\n");
    exit(1);
}

$path = dirname(__DIR__) . '/public/' . ltrim($relPath, '/');

if (!is_file($path)) {
    fwrite(STDERR, "File not found: {$path}\n");
    exit(1);
}

if (!function_exists('imagecreatefrompng')) {
    fwrite(STDERR, "GD extension with PNG support is required.\n");
    exit(1);
}

$src = imagecreatefrompng($path);
if ($src === false) {
    fwrite(STDERR, "Could not read PNG: {$path}\n");
    exit(1);
}

$width = imagesx($src);
$height = imagesy($src);

// Near-white threshold, used as a fallback when the image has no meaningful alpha
// channel (i.e. it was flattened onto a white canvas rather than exported transparent).
$whiteThreshold = 250;

// Pass 1: does this PNG actually carry a real transparency mask? A logo meant for
// dark backgrounds is often WHITE artwork on a transparent canvas - if we only
// checked "is this pixel near-white" we'd wrongly classify the white artwork
// itself as background. So: if any pixel is meaningfully transparent, trust the
// alpha channel alone for the bounding box; only fall back to color-based
// near-white detection for a fully-opaque (flattened) image.
$hasAlpha = false;
for ($y = 0; $y < $height && !$hasAlpha; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $alpha = (imagecolorat($src, $x, $y) >> 24) & 0x7F;
        if ($alpha > 10) {
            $hasAlpha = true;
            break;
        }
    }
}

$minX = $width;
$minY = $height;
$maxX = -1;
$maxY = -1;

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgba = imagecolorat($src, $x, $y);
        $alpha = ($rgba >> 24) & 0x7F; // 0 (opaque) .. 127 (fully transparent) in GD

        if ($hasAlpha) {
            $isBackground = $alpha > 10;
        } else {
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;
            $isBackground = $r >= $whiteThreshold && $g >= $whiteThreshold && $b >= $whiteThreshold;
        }

        if (!$isBackground) {
            if ($x < $minX) $minX = $x;
            if ($x > $maxX) $maxX = $x;
            if ($y < $minY) $minY = $y;
            if ($y > $maxY) $maxY = $y;
        }
    }
}

if ($maxX < 0) {
    fwrite(STDERR, "No content found (image appears blank) - leaving file untouched.\n");
    exit(1);
}

$contentWidth = $maxX - $minX + 1;
$contentHeight = $maxY - $minY + 1;

$padX = (int) round($contentWidth * ($paddingPercent / 100));
$padY = (int) round($contentHeight * ($paddingPercent / 100));

$cropX = max(0, $minX - $padX);
$cropY = max(0, $minY - $padY);
$cropW = min($width - $cropX, $contentWidth + 2 * $padX);
$cropH = min($height - $cropY, $contentHeight + 2 * $padY);

printf(
    "Source: %dx%d, content bbox: (%d,%d)-(%d,%d) [%dx%d], alpha channel: %s\n",
    $width,
    $height,
    $minX,
    $minY,
    $maxX,
    $maxY,
    $contentWidth,
    $contentHeight,
    $hasAlpha ? 'yes' : 'no (flattened/near-white background)'
);
printf("Cropping to (%d,%d) size %dx%d with %.0f%% padding\n", $cropX, $cropY, $cropW, $cropH, $paddingPercent);

$dst = imagecreatetruecolor($cropW, $cropH);
imagealphablending($dst, false);
imagesavealpha($dst, true);
$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
imagefilledrectangle($dst, 0, 0, $cropW, $cropH, $transparent);
imagealphablending($dst, true);

imagecopy($dst, $src, 0, 0, $cropX, $cropY, $cropW, $cropH);

$backupPath = $path . '.bak';
if (!is_file($backupPath)) {
    copy($path, $backupPath);
    echo "Backup saved: {$backupPath}\n";
} else {
    echo "Backup already exists, not overwriting: {$backupPath}\n";
}

imagepng($dst, $path);
imagedestroy($src);
imagedestroy($dst);

echo "Trimmed and saved: {$path}\n";
