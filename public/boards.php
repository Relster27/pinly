<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();

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
$stmt->execute([current_user_id()]);
$boards = $stmt->fetchAll();

$activeTab = 'boards';
$pageTitle = 'Your boards · Pinly';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="margin-top:28px; display:flex; align-items:center; justify-content:space-between;">
  <h1 class="section-title" style="margin:0;">Your boards</h1>
  <a class="btn-primary" href="board_create.php">+ New board</a>
</section>

<section class="section">
  <?php if (!$boards): ?>
    <p class="boards-empty">You don't have any boards yet. Create one to start organizing pins.</p>
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

<?php require __DIR__ . '/../includes/footer.php'; ?>
