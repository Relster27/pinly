<?php
require __DIR__ . '/../includes/bootstrap.php';

$boardId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT boards.id, boards.name, boards.user_id, users.username
     FROM boards JOIN users ON users.id = boards.user_id
     WHERE boards.id = ?'
);
$stmt->execute([$boardId]);
$board = $stmt->fetch();

if (!$board) {
    http_response_code(404);
    $activeTab = 'boards';
    $pageTitle = 'Board not found · Pinly';
    require __DIR__ . '/../includes/header.php';
    echo '<section class="section"><p class="masonry-empty">That board does not exist.</p></section>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare(
    'SELECT pins.id, pins.image_path, pins.title
     FROM board_pins
     JOIN pins ON pins.id = board_pins.pin_id
     WHERE board_pins.board_id = ?
     ORDER BY board_pins.added_at DESC'
);
$stmt->execute([$boardId]);
$pins = $stmt->fetchAll();

$activeTab = 'boards';
$pageTitle = e($board['name']) . ' · Pinly';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="margin-top:28px;">
  <h1 class="section-title" style="margin:0;"><?= e($board['name']) ?></h1>
  <p class="byline" style="color:var(--ink-soft); font-size:0.9rem;">by <?= e($board['username']) ?> · <?= pin_count_label(count($pins)) ?></p>
</section>

<section class="section">
  <?php if (!$pins): ?>
    <p class="masonry-empty">No pins in this board yet.</p>
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
