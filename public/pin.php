<?php
require __DIR__ . '/../includes/bootstrap.php';

$pinId = (int) ($_GET['id'] ?? 0);

function fetch_pin(PDO $pdo, int $pinId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT pins.*, users.username
         FROM pins JOIN users ON users.id = pins.user_id
         WHERE pins.id = ?'
    );
    $stmt->execute([$pinId]);
    $pin = $stmt->fetch();
    return $pin ?: null;
}

$pin = fetch_pin($pdo, $pinId);
if (!$pin) {
    http_response_code(404);
    $activeTab = '';
    $pageTitle = 'Pin not found · Pinly';
    require __DIR__ . '/../includes/header.php';
    echo '<section class="section"><p class="masonry-empty">That pin does not exist.</p></section>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$isOwner = current_user_id() === (int) $pin['user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update' && $isOwner) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || strlen($title) > 150) {
            $errors[] = 'Please enter a title (up to 150 characters).';
        } else {
            $stmt = $pdo->prepare('UPDATE pins SET title = ?, description = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$title, $description !== '' ? $description : null, $pinId, current_user_id()]);
            $pin = fetch_pin($pdo, $pinId);
        }
    }

    if ($action === 'delete' && $isOwner) {
        $stmt = $pdo->prepare('DELETE FROM pins WHERE id = ? AND user_id = ?');
        $stmt->execute([$pinId, current_user_id()]);
        $imagePath = __DIR__ . '/' . $pin['image_path'];
        if (is_file($imagePath)) {
            unlink($imagePath);
        }
        redirect('index.php');
    }
}

$stmt = $pdo->prepare(
    'SELECT boards.id, boards.name
     FROM board_pins JOIN boards ON boards.id = board_pins.board_id
     WHERE board_pins.pin_id = ?
     ORDER BY boards.name'
);
$stmt->execute([$pinId]);
$pinBoards = $stmt->fetchAll();

$myBoardsToAdd = [];
if (current_user_id() !== null) {
    $pinBoardIds = array_column($pinBoards, 'id');
    $stmt = $pdo->prepare('SELECT id, name FROM boards WHERE user_id = ? ORDER BY name');
    $stmt->execute([current_user_id()]);
    $myBoardsToAdd = array_filter($stmt->fetchAll(), fn ($b) => !in_array($b['id'], $pinBoardIds));
}

$activeTab = '';
$pageTitle = e($pin['title']) . ' · Pinly';
require __DIR__ . '/../includes/header.php';
?>

<div class="pin-detail">
  <div class="art">
    <img src="<?= e($pin['image_path']) ?>" alt="<?= e($pin['title']) ?>">
  </div>
  <div class="meta">
    <h1><?= e($pin['title']) ?></h1>
    <p class="byline">by <?= e($pin['username']) ?></p>
    <?php if ($pin['description']): ?>
      <p class="description"><?= nl2br(e($pin['description'])) ?></p>
    <?php endif; ?>

    <?php if ($pinBoards): ?>
      <div class="board-chip-list">
        <?php foreach ($pinBoards as $b): ?>
          <a class="board-chip" href="board.php?id=<?= (int) $b['id'] ?>"><?= e($b['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
      <div class="error-text"><?= e($error) ?></div>
    <?php endforeach; ?>

    <?php if (current_user_id() !== null): ?>
      <?php if ($myBoardsToAdd): ?>
        <form method="post" action="board_add_pin.php" style="margin-bottom:20px;">
          <?= csrf_field() ?>
          <input type="hidden" name="pin_id" value="<?= (int) $pinId ?>">
          <div class="field">
            <label for="board_id">Add to board</label>
            <select id="board_id" name="board_id" style="border:1px solid var(--border); border-radius:12px; padding:10px 14px; font-family:'Inter'; font-size:0.92rem;">
              <?php foreach ($myBoardsToAdd as $b): ?>
                <option value="<?= (int) $b['id'] ?>"><?= e($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn-primary">Save to board</button>
        </form>
      <?php elseif (current_user_id() !== null): ?>
        <p class="form-note">Already saved to all your boards, or <a href="board_create.php">create a new board</a>.</p>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($isOwner): ?>
      <h2 class="section-title">Edit pin</h2>
      <form method="post" action="pin.php?id=<?= (int) $pinId ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <div class="field">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" value="<?= e($pin['title']) ?>" required maxlength="150">
        </div>
        <div class="field">
          <label for="description">Description</label>
          <textarea id="description" name="description"><?= e($pin['description'] ?? '') ?></textarea>
        </div>
        <div class="owner-actions">
          <button type="submit" class="btn-primary">Save changes</button>
        </div>
      </form>
      <form method="post" action="pin.php?id=<?= (int) $pinId ?>" onsubmit="return confirm('Delete this pin? This cannot be undone.');" style="margin-top:12px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn-danger">Delete pin</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
