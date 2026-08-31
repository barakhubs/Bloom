<?php

/**
 * Generates a simple placeholder favicon in the brand primary color, since no real
 * logo/favicon exists yet (index.html references img/favicon.ico but the file was
 * never present in the template). This is a stopgap only - replace it with the real
 * org logo via the admin Settings favicon upload once feature/admin-settings exists.
 *
 * Usage: php tools/generate-placeholder-favicon.php
 */

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD extension not available.\n");
    exit(1);
}

$primaryHex = '#7ec11c';
$secondaryHex = '#003893';

function hexToRgb(string $hex): array
{
    $hex = ltrim($hex, '#');

    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function pngForSize(int $size, array $primary, array $secondary): string
{
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    $bg = imagecolorallocate($im, ...$primary);
    imagefilledellipse($im, (int) ($size / 2), (int) ($size / 2), $size, $size, $bg);

    // simple "B" monogram-ish mark: a secondary-colored ring inset, standing in for a
    // real logomark until the org's actual logo is uploaded via the back office
    $ring = imagecolorallocate($im, ...$secondary);
    $inset = (int) round($size * 0.22);
    imagefilledellipse($im, (int) ($size / 2), (int) ($size / 2), $size - $inset * 2, $size - $inset * 2, $ring);
    imagefilledellipse($im, (int) ($size / 2), (int) ($size / 2), (int) (($size - $inset * 2) * 0.55), (int) (($size - $inset * 2) * 0.55), $bg);

    ob_start();
    imagepng($im);
    $data = ob_get_clean();
    imagedestroy($im);

    return $data;
}

function wrapPngAsIco(array $pngsBySize): string
{
    $count = count($pngsBySize);
    $header = pack('vvv', 0, 1, $count);

    $dirEntries = '';
    $imageData = '';
    $offset = 6 + $count * 16;

    foreach ($pngsBySize as $size => $png) {
        $w = $size >= 256 ? 0 : $size;
        $h = $size >= 256 ? 0 : $size;
        $dirEntries .= pack('CCCCvvVV', $w, $h, 0, 0, 1, 32, strlen($png), $offset);
        $imageData .= $png;
        $offset += strlen($png);
    }

    return $header . $dirEntries . $imageData;
}

$primary = hexToRgb($primaryHex);
$secondary = hexToRgb($secondaryHex);

$sizes = [16, 32, 48];
$pngs = [];
foreach ($sizes as $size) {
    $pngs[$size] = pngForSize($size, $primary, $secondary);
}

$ico = wrapPngAsIco($pngs);

$outPath = dirname(__DIR__) . '/public/img/favicon.ico';
file_put_contents($outPath, $ico);

echo "Wrote {$outPath} (" . strlen($ico) . " bytes, sizes: " . implode(',', $sizes) . ")\n";
