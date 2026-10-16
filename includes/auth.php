<?php

function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function require_login(): void
{
    if (current_user_id() === null) {
        redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function current_user(PDO $pdo): ?array
{
    $id = current_user_id();
    if ($id === null) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        exit('Invalid request.');
    }
}
