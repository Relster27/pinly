<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();

$errors = [];
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Please enter a board name (up to 100 characters).';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO boards (user_id, name) VALUES (?, ?)');
            $stmt->execute([current_user_id(), $name]);
            redirect('board.php?id=' . $pdo->lastInsertId());
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'You already have a board with that name.';
            } else {
                throw $e;
            }
        }
    }
}

$activeTab = 'boards';
$pageTitle = 'New board · Pinly';
require __DIR__ . '/../includes/header.php';
?>
<div class="section">
  <div class="form-card">
    <h1>Create a board</h1>
    <?php foreach ($errors as $error): ?>
      <div class="error-text"><?= e($error) ?></div>
    <?php endforeach; ?>
    <form method="post" action="board_create.php">
      <?= csrf_field() ?>
      <div class="field">
        <label for="name">Board name</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" required maxlength="100">
      </div>
      <button type="submit" class="btn-primary">Create board</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
