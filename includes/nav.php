<?php
$activeTab = $activeTab ?? '';
$viewer = current_user($pdo);
?>
<div class="topbar">
  <a class="brand" href="index.php">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
      <path d="M12 2C7.58 2 4 5.58 4 10c0 3.5 2.3 6.47 5.5 7.5L11 22l1-5.1c.33.03.66.1 1 .1 4.42 0 8-3.58 8-8s-3.58-7-9-7z" fill="currentColor"/>
    </svg>
    Pinly
  </a>
  <form class="search" action="index.php" method="get">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
    </svg>
    <input type="text" name="q" placeholder="Search pins" value="<?= e($_GET['q'] ?? '') ?>">
  </form>
  <div class="nav-group">
    <nav class="nav-tabs">
      <a class="tab<?= $activeTab === 'feed' ? ' active' : '' ?>" href="index.php">Feed</a>
      <a class="tab<?= $activeTab === 'boards' ? ' active' : '' ?>" href="boards.php">Boards</a>
      <a class="tab<?= $activeTab === 'upload' ? ' active' : '' ?>" href="upload.php">Upload</a>
      <a class="tab<?= $activeTab === 'profile' ? ' active' : '' ?>" href="profile.php">Profile</a>
    </nav>
    <div class="nav-actions">
      <?php if ($viewer): ?>
        <a class="text-link" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="text-link" href="login.php">Login</a>
        <a class="text-link" href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</div>
