<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();

$errors = [];
$title = '';
$description = '';

$stmt = $pdo->prepare('SELECT id, name FROM boards WHERE user_id = ? ORDER BY name');
$stmt->execute([current_user_id()]);
$myBoards = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $boardIds = array_map('intval', $_POST['board_ids'] ?? []);

    if ($title === '' || strlen($title) > 150) {
        $errors[] = 'Please enter a title (up to 150 characters).';
    }

    $mime = null;
    if (!$errors) {
        $mime = validate_upload($_FILES['image'] ?? []);
        if ($mime === null) {
            $errors[] = 'Please choose a valid image (JPEG, PNG, WEBP or GIF, up to 5MB).';
        }
    }

    if (!$errors) {
        $imagePath = save_uploaded_file($_FILES['image'], $mime);

        $stmt = $pdo->prepare('INSERT INTO pins (user_id, image_path, title, description) VALUES (?, ?, ?, ?)');
        $stmt->execute([current_user_id(), $imagePath, $title, $description !== '' ? $description : null]);
        $pinId = (int) $pdo->lastInsertId();

        if ($boardIds) {
            $ownBoardIds = array_column($myBoards, 'id');
            $validBoardIds = array_intersect($boardIds, $ownBoardIds);
            $stmt = $pdo->prepare('INSERT IGNORE INTO board_pins (board_id, pin_id) VALUES (?, ?)');
            foreach ($validBoardIds as $boardId) {
                $stmt->execute([$boardId, $pinId]);
            }
        }

        redirect('pin.php?id=' . $pinId);
    }
}

$activeTab = 'upload';
$pageTitle = 'Upload a pin · Pinly';
require __DIR__ . '/../includes/header.php';
?>
<div class="section">
  <div class="form-card">
    <h1>Upload a pin</h1>
    <?php foreach ($errors as $error): ?>
      <div class="error-text"><?= e($error) ?></div>
    <?php endforeach; ?>
    <form method="post" action="upload.php" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="field">
        <label for="image">Image</label>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required>
      </div>
      <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($title) ?>" required maxlength="150">
      </div>
      <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= e($description) ?></textarea>
      </div>
      <?php if ($myBoards): ?>
        <div class="field">
          <label>Add to boards (optional)</label>
          <div class="checkbox-list">
            <?php foreach ($myBoards as $board): ?>
              <label>
                <input type="checkbox" name="board_ids[]" value="<?= (int) $board['id'] ?>">
                <?= e($board['name']) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      <button type="submit" class="btn-primary">Upload pin</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
