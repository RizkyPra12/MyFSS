<?php
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if(Auth::login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: ?page=dashboard');
        exit;
    }
    $_SESSION['error'] = 'Invalid username or password';
    header('Location: ?page=login');
    exit;
}
?>
<div style="max-width:400px;margin:50px auto;padding:30px;background:#14141f;border-radius:12px;border:1px solid #77b8f0;">
  <?php if($logo): ?>
  <img src="<?=h($logo)?>" style="max-width:120px;display:block;margin:0 auto 20px;">
  <?php endif; ?>
  <h1 style="text-align:center;color:#77b8f0;"><?=SITE_NAME?></h1>
  <form method="POST" style="margin-top:30px;">
    <input type="hidden" name="action" value="login">
    <div style="margin-bottom:15px;">
      <input type="text" name="username" placeholder="Username" required 
             style="width:100%;padding:12px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    </div>
    <div style="margin-bottom:20px;">
      <input type="password" name="password" placeholder="Password" required 
             style="width:100%;padding:12px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    </div>
    <button type="submit" style="width:100%;padding:12px;background:#77b8f0;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;">Login</button>
  </form>
  <p style="text-align:center;margin-top:20px;color:#999;">
    <a href="?page=register" style="color:#77b8f0;">Register</a>
  </p>
</div>
