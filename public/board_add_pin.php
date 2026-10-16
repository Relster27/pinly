<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

$pinId = (int) ($_POST['pin_id'] ?? 0);
$boardId = (int) ($_POST['board_id'] ?? 0);

$stmt = $pdo->prepare('SELECT id FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, current_user_id()]);

if ($stmt->fetch()) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO board_pins (board_id, pin_id) VALUES (?, ?)');
    $stmt->execute([$boardId, $pinId]);
}

redirect('pin.php?id=' . $pinId);
