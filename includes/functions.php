<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Validates an uploaded file array ($_FILES['...']).
 * Returns the detected MIME type on success, or null on failure.
 */
function validate_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset(ALLOWED_MIME_TO_EXT[$mime])) {
        return null;
    }
    if (@getimagesize($file['tmp_name']) === false) {
        return null;
    }

    return $mime;
}

/**
 * CSS aspect-ratio value ("w / h") for a pin's stored image, so its masonry
 * card can reserve the right height before the photo itself has loaded.
 */
function pin_aspect_ratio(string $relativePath): string
{
    $size = @getimagesize(__DIR__ . '/../public/' . $relativePath);
    if (!$size || $size[0] <= 0 || $size[1] <= 0) {
        return '1 / 1';
    }
    return $size[0] . ' / ' . $size[1];
}

function pin_count_label(int $count): string
{
    return $count . ' pin' . ($count === 1 ? '' : 's');
}

function generate_unique_filename(string $mime): string
{
    $ext = ALLOWED_MIME_TO_EXT[$mime];
    return bin2hex(random_bytes(16)) . '.' . $ext;
}

/**
 * Moves a validated upload into place and returns its DB-stored relative path.
 */
function save_uploaded_file(array $file, string $mime): string
{
    $filename = generate_unique_filename($mime);
    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename);
    return UPLOAD_URL_PREFIX . $filename;
}
