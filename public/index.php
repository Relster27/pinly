<?php
require __DIR__ . '/../includes/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 24;
$offset = ($page - 1) * $perPage;

if ($q !== '') {
    $stmt = $pdo->prepare(
        'SELECT pins.id, pins.image_path, pins.title
         FROM pins
         WHERE pins.title LIKE :q
         ORDER BY pins.created_at DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
} else {
    $stmt = $pdo->prepare(
        'SELECT pins.id, pins.image_path, pins.title
         FROM pins
         ORDER BY pins.created_at DESC
         LIMIT :limit OFFSET :offset'
    );
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pins = $stmt->fetchAll();

$boards = [];
if (current_user_id() !== null) {
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
}

$activeTab = 'feed';
$pageTitle = 'Pinly';
require __DIR__ . '/../includes/header.php';
?>

<?php if ($boards): ?>
<section class="section">
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
</section>
<section class="section"><hr class="divider"></section>
<?php endif; ?>

<section class="section">
  <?php if (!$pins): ?>
    <p class="masonry-empty"><?= $q !== '' ? 'No pins match "' . e($q) . '".' : 'No pins yet. Be the first to upload one.' ?></p>
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
