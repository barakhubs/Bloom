<?php

namespace App\Core;

class Upload
{
    /**
     * Validates and stores an uploaded file under public/uploads/{subfolder}.
     * Returns the stored relative path (e.g. "uploads/logos/ab12cd34.png"), or
     * null if no file was submitted (not an error - the field is optional).
     * Throws RuntimeException on any validation failure.
     */
    public static function store(array $file, string $subfolder, array $allowedExtensions, int $maxBytes = 2097152): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (error code ' . $file['error'] . ').');
        }

        if ($file['size'] > $maxBytes) {
            throw new \RuntimeException('File is too large (max ' . round($maxBytes / 1024) . ' KB).');
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            throw new \RuntimeException('File type not allowed (allowed: ' . implode(', ', $allowedExtensions) . ').');
        }

        // ICO doesn't have one canonical finfo MIME across environments, so the
        // extension + size checks above are what gate it; raster formats get an extra
        // check that file content actually matches the claimed extension.
        $allowedMimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        if (isset($allowedMimes[$ext])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if ($mime !== $allowedMimes[$ext]) {
                throw new \RuntimeException('File content does not match its extension.');
            }
        }

        // SVG is XML, not a raster format finfo can content-check the same way, and it
        // can carry executable script - <script>, event-handler attributes, javascript:
        // URIs. A browser won't run any of that when the file is only ever referenced
        // via <img src>, but a visitor who opens the uploaded file's URL directly would
        // execute it in this site's origin. Reject anything that looks executable
        // rather than trying to sanitize it.
        if ($ext === 'svg') {
            $contents = file_get_contents($file['tmp_name']);
            if ($contents === false || !self::isSafeSvg($contents)) {
                throw new \RuntimeException('SVG file contains disallowed content (scripts or event handlers).');
            }
        }

        $dir = dirname(__DIR__, 2) . '/public/uploads/' . $subfolder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = bin2hex(random_bytes(8)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            throw new \RuntimeException('Failed to save the uploaded file.');
        }

        return 'uploads/' . $subfolder . '/' . $filename;
    }

    /**
     * Removes a previously-stored upload (e.g. when replaced by a new one, or
     * when the owning record is deleted). Silently no-ops if the file doesn't
     * exist - never throws, since a missing file here isn't a real failure.
     */
    public static function delete(string $relativePath): void
    {
        $full = dirname(__DIR__, 2) . '/public/' . ltrim($relativePath, '/');
        if (is_file($full)) {
            @unlink($full);
        }
    }

    /**
     * Rejects SVG content that could execute script if the file were opened
     * directly: <script> elements, on*="" event-handler attributes, and
     * javascript: URIs (e.g. in an <a href> or xlink:href inside the SVG).
     */
    private static function isSafeSvg(string $contents): bool
    {
        if (stripos($contents, '<script') !== false) {
            return false;
        }

        if (preg_match('/\son\w+\s*=/i', $contents) === 1) {
            return false;
        }

        if (stripos($contents, 'javascript:') !== false) {
            return false;
        }

        return true;
    }
}
