<?php
require __DIR__ . '/../includes/bootstrap.php';

if (current_user_id() !== null) {
    redirect('index.php');
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || strlen($username) > 50) {
        $errors[] = 'Please enter a username (up to 50 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'That username or email is already taken.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);

        $_SESSION['user_id'] = (int) $pdo->lastInsertId();
        session_regenerate_id(true);
        redirect('index.php');
    }
}

$pageTitle = 'Register · Pinly';
require __DIR__ . '/../includes/header.php';
?>
<div class="section">
  <div class="form-card">
    <h1>Create your account</h1>
    <?php foreach ($errors as $error): ?>
      <div class="error-text"><?= e($error) ?></div>
    <?php endforeach; ?>
    <form method="post" action="register.php">
      <?= csrf_field() ?>
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" required maxlength="50" autocomplete="username">
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
      </div>
      <div class="field">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required minlength="8" autocomplete="new-password">
      </div>
      <button type="submit" class="btn-primary">Create account</button>
    </form>
    <p class="form-note">Already have an account? <a href="login.php">Log in</a>.</p>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
