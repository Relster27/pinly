<?php

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL_PREFIX', 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_MIME_TO_EXT', [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
]);

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    getenv('DB_HOST'),
    getenv('DB_PORT'),
    getenv('DB_NAME')
);

try {
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed.');
}

require __DIR__ . '/functions.php';
require __DIR__ . '/auth.php';
