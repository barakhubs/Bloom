<?php

/**
 * Recolors the compiled public/css/bootstrap.min.css primary/secondary palette.
 *
 * There is no Sass toolchain in this project (vanilla CSS only, no npm/build step),
 * and this compiled Bootstrap build has zero var(--bs-primary) usage - every
 * primary/secondary color, including every hover/focus/active tint-shade Bootstrap's
 * Sass compiler baked in at build time, is a literal hex/rgb()/rgba() value. A plain
 * find-and-replace of the two base hex codes would leave ~20 derived hover/focus/
 * active/box-shadow states rendering in the OLD palette.
 *
 * This script instead:
 *  1. Replaces the base hex codes and their literal rgba() alpha forms directly.
 *  2. Finds every rgb()/rgba() value in the file that is a Bootstrap
 *     tint-color()/shade-color() derivative of the OLD base colors (verified against
 *     Bootstrap's real default weight set, cross-checked by exact substring
 *     extraction against the compiled CSS - see feature/brand-theme commit message
 *     for how these weights were derived), and recomputes the equivalent tint/shade
 *     against the NEW base colors using the same weight.
 *  3. Leaves every other color in the file (success/warning/danger/dark/light/info,
 *     grayscale, etc) untouched - verified by before/after occurrence-count diffing.
 *
 * Safe to re-run: once the old hex codes are gone, the base substitutions and the
 * derived-value matching (which only matches against the OLD bases) both become
 * no-ops.
 *
 * Usage: php tools/recolor-bootstrap.php [oldPrimary] [newPrimary] [oldSecondary] [newSecondary]
 *   Defaults to the Charitize template's original colors -> Bloom Beyond Borders brand colors.
 */

$oldPrimaryHex   = $argv[1] ?? '#ffac00';
$newPrimaryHex   = $argv[2] ?? '#7ec11c';
$oldSecondaryHex = $argv[3] ?? '#1a685b';
$newSecondaryHex = $argv[4] ?? '#003893';

$path = dirname(__DIR__) . '/public/css/bootstrap.min.css';
$css = file_get_contents($path);
$original = $css;

function hexToRgb(string $hex): array
{
    $hex = ltrim($hex, '#');

    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function rgbToHex(array $rgb): string
{
    return sprintf('#%02x%02x%02x', ...array_map('intval', $rgb));
}

function shade(array $rgb, float $weightPercent): array
{
    $w = $weightPercent / 100;

    return array_map(fn ($c) => $c * (1 - $w), $rgb);
}

function tint(array $rgb, float $weightPercent): array
{
    $w = $weightPercent / 100;

    return array_map(fn ($c) => $c * (1 - $w) + 255 * $w, $rgb);
}

function fmtNum(float $n): string
{
    $s = number_format($n, 2, '.', '');
    $s = rtrim(rtrim($s, '0'), '.');

    return $s === '' ? '0' : $s;
}

function fmtRgb(array $rgb): string
{
    return implode(',', array_map('fmtNum', $rgb));
}

$bases = [
    'primary' => [hexToRgb($oldPrimaryHex), hexToRgb($newPrimaryHex)],
    'secondary' => [hexToRgb($oldSecondaryHex), hexToRgb($newSecondaryHex)],
];

// Bootstrap's real default tint/shade weight set (button hover/active, link hover,
// alert/list-group bg/border/color scales, accordion active bg), extended with the
// 4 extra weights found by a broadened 1-99% diagnostic scan against this specific
// compiled build (alert-link and accordion variants using non-default scales).
$weights = [10, 15, 20, 25, 40, 50, 52, 60, 68, 70, 80, 90];

// Match both rgb(r,g,b) and rgba(r,g,b,alpha) - Bootstrap bakes derived tint/shade
// values into box-shadow focus rings as rgba() with an alpha channel too.
preg_match_all('/rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)\s*(?:,\s*([\d.]+)\s*)?\)/', $css, $matches, PREG_SET_ORDER);

$replacements = [];
$seen = [];
foreach ($matches as $m) {
    $full = $m[0];
    if (isset($seen[$full])) {
        continue;
    }
    $seen[$full] = true;
    $observed = [(float) $m[1], (float) $m[2], (float) $m[3]];
    $alpha = $m[4] ?? null;

    foreach ($bases as $old_new) {
        [$old, $new] = $old_new;
        foreach (['shade', 'tint'] as $fn) {
            foreach ($weights as $w) {
                $candidate = $fn === 'shade' ? shade($old, $w) : tint($old, $w);
                $diff = abs($candidate[0] - $observed[0]) + abs($candidate[1] - $observed[1]) + abs($candidate[2] - $observed[2]);
                if ($diff < 0.05) {
                    $newCandidate = $fn === 'shade' ? shade($new, $w) : tint($new, $w);
                    $prefix = $alpha !== null ? 'rgba' : 'rgb';
                    $suffix = $alpha !== null ? (fmtRgb($newCandidate) . ',' . $alpha) : fmtRgb($newCandidate);
                    $replacements[$full] = $prefix . '(' . $suffix . ')';
                    continue 3;
                }
            }
        }
    }
}

echo count($replacements) . " derived rgb()/rgba() values matched and will be replaced.\n";

// Longer keys first so no partial-substring collisions during strtr.
uksort($replacements, fn ($a, $b) => strlen($b) <=> strlen($a));
$css = strtr($css, $replacements);

foreach ($bases as $old_new) {
    [$old, $new] = $old_new;
    $oldHex = rgbToHex($old);
    $newHex = rgbToHex($new);
    $css = str_ireplace($oldHex, $newHex, $css);

    $oldRgbCsv = implode(',', $old);
    $newRgbCsv = implode(',', $new);
    $css = str_replace("rgba({$oldRgbCsv},", "rgba({$newRgbCsv},", $css);
    $oldRgbSpaced = implode(', ', $old);
    $newRgbSpaced = implode(', ', $new);
    $css = str_replace("rgba({$oldRgbSpaced},", "rgba({$newRgbSpaced},", $css);
}

file_put_contents($path, $css);

echo 'File size before: ' . strlen($original) . ' bytes, after: ' . strlen($css) . " bytes\n";
echo "Done. Review with `git diff public/css/bootstrap.min.css` before committing.\n";
