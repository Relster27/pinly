<?php
require __DIR__ . '/../includes/bootstrap.php';

$username = trim($_GET['username'] ?? '');

if ($username === '') {
    require_login();
    $stmt = $pdo->prepare('SELECT id, username, created_at FROM users WHERE id = ?');
    $stmt->execute([current_user_id()]);
} else {
    $stmt = $pdo->prepare('SELECT id, username, created_at FROM users WHERE username = ?');
    $stmt->execute([$username]);
}
$profileUser = $stmt->fetch();

if (!$profileUser) {
    http_response_code(404);
    $activeTab = 'profile';
    $pageTitle = 'User not found · Pinly';
    require __DIR__ . '/../includes/header.php';
    echo '<section class="section"><p class="masonry-empty">That user does not exist.</p></section>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare(
    'SELECT boards.id, boards.name,
            COUNT(board_pins.pin_id) AS pin_count,
            (SELECT p.image_path FROM board_pins bp
               JOIN pins p ON p.id = bp.pin_id
              WHERE bp.board_id = boards.id
              ORDER BY bp.added_at DESC LIMIT 1) AS cover_image
     FROM boards
     LEFT JOIN board_pins ON board_pins.board_id = boards.id
     WHERE boards.user_id = ?
     GROUP BY boards.id
     ORDER BY boards.created_at DESC'
);
$stmt->execute([$profileUser['id']]);
$boards = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT id, image_path, title FROM pins WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$profileUser['id']]);
$pins = $stmt->fetchAll();

$activeTab = 'profile';
$pageTitle = e($profileUser['username']) . ' · Pinly';
require __DIR__ . '/../includes/header.php';
?>

<div class="profile-header">
  <div class="avatar"><?= e(strtoupper(substr($profileUser['username'], 0, 1))) ?></div>
  <div>
    <h1><?= e($profileUser['username']) ?></h1>
    <p class="sub"><?= count($boards) ?> board<?= count($boards) === 1 ? '' : 's' ?> · <?= pin_count_label(count($pins)) ?></p>
  </div>
</div>

<section class="section">
  <?php if (!$boards): ?>
    <p class="boards-empty">No boards yet.</p>
  <?php else: ?>
    <div class="boards-row">
      <?php foreach ($boards as $board): ?>
        <?php if ($board['cover_image']): ?>
          <a class="board-card photo" href="board.php?id=<?= (int) $board['id'] ?>">
            <div class="art"><img data-src="<?= e($board['cover_image']) ?>" alt=""></div>
            <h3><?= e($board['name']) ?></h3>
            <span class="count"><?= pin_count_label((int) $board['pin_count']) ?></span>
          </a>
        <?php else: ?>
          <a class="board-card empty" href="board.php?id=<?= (int) $board['id'] ?>">
            <h3><?= e($board['name']) ?></h3>
            <span class="count"><?= pin_count_label((int) $board['pin_count']) ?></span>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="section"><hr class="divider"></section>

<section class="section">
  <?php if (!$pins): ?>
    <p class="masonry-empty">No pins yet.</p>
  <?php else: ?>
    <div class="masonry">
      <?php foreach ($pins as $pin): ?>
        <div class="pin-card">
          <a class="art-link" href="pin.php?id=<?= (int) $pin['id'] ?>">
            <div class="art" style="aspect-ratio: <?= pin_aspect_ratio($pin['image_path']) ?>;"><img data-src="<?= e($pin['image_path']) ?>" alt="<?= e($pin['title']) ?>"></div>
          </a>
          <a class="icon-btn" href="<?= e($pin['image_path']) ?>" download aria-label="Download" title="Download">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/>
            </svg>
          </a>
          <a class="save-btn" href="pin.php?id=<?= (int) $pin['id'] ?>">Save</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
