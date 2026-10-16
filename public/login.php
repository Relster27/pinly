<?php
require __DIR__ . '/../includes/bootstrap.php';

if (current_user_id() !== null) {
    redirect('index.php');
}

$errors = [];
$identifier = '';
$redirectTo = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
if (str_contains($redirectTo, '://') || str_starts_with($redirectTo, '//')) {
    $redirectTo = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Incorrect username/email or password.';
    } else {
        $_SESSION['user_id'] = (int) $user['id'];
        session_regenerate_id(true);
        redirect($redirectTo);
    }
}

$pageTitle = 'Log in · Pinly';
require __DIR__ . '/../includes/header.php';
?>
<div class="section">
  <div class="form-card">
    <h1>Log in</h1>
    <?php foreach ($errors as $error): ?>
      <div class="error-text"><?= e($error) ?></div>
    <?php endforeach; ?>
    <form method="post" action="login.php">
      <?= csrf_field() ?>
      <input type="hidden" name="redirect" value="<?= e($redirectTo) ?>">
      <div class="field">
        <label for="identifier">Username or email</label>
        <input type="text" id="identifier" name="identifier" value="<?= e($identifier) ?>" required autocomplete="username">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-primary">Log in</button>
    </form>
    <p class="form-note">New here? <a href="register.php">Create an account</a>.</p>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
