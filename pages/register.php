<?php
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if(strlen($username) < 3) {
        $_SESSION['error'] = 'Username must be at least 3 characters';
    } elseif($password !== $confirm) {
        $_SESSION['error'] = 'Passwords do not match';
    } elseif(strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters';
    } elseif(User::exists($username)) {
        $_SESSION['error'] = 'Username already taken';
    } else {
        try {
            User::create([
                'username' => $username,
                'password' => $password,
                'country_name' => $_POST['country_name'] ?? '',
                'government_form' => $_POST['government_form'] ?? '',
                'ideology' => $_POST['ideology'] ?? '',
                'phone_number' => $_POST['phone_number'] ?? '',
                'email' => $_POST['email'] ?? null,
                'flag_url' => $_POST['flag_url'] ?? null,
                'age_range' => $_POST['age_range'] ?? '18-25'
            ]);
            $_SESSION['success'] = 'Registration successful!';
            header('Location: ?page=login');
            exit;
        } catch(Exception $e) {
            $_SESSION['error'] = 'Registration failed';
        }
    }
    header('Location: ?page=register');
    exit;
}
?>
<div style="max-width:500px;margin:30px auto;padding:30px;background:#14141f;border-radius:12px;border:1px solid #77b8f0;">
  <h1 style="text-align:center;color:#77b8f0;">Register</h1>
  <div style="background:rgba(255,170,0,0.1);padding:10px;margin:15px 0;border-radius:4px;color:#ffaa00;font-size:13px;">
    ⚠️ Username is permanent
  </div>
  <form method="POST">
    <input type="hidden" name="action" value="register">
    <input type="text" name="username" placeholder="Username *" required minlength="3" style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    <input type="tel" name="phone_number" placeholder="Phone *" required style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    <input type="password" name="password" placeholder="Password *" required minlength="6" style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    <input type="password" name="confirm_password" placeholder="Confirm Password *" required style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    <input type="text" name="country_name" placeholder="Country *" required style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    <input type="email" name="email" placeholder="Email" style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    <input type="url" name="flag_url" placeholder="Flag URL" style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
    <select name="government_form" required style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
      <option value="">Government *</option>
      <?php foreach($govForms as $g): ?><option><?=h($g)?></option><?php endforeach; ?>
    </select>
    <select name="ideology" required style="width:100%;padding:10px;margin-bottom:10px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
      <option value="">Ideology *</option>
      <?php foreach($ideologies as $i): ?><option><?=h($i)?></option><?php endforeach; ?>
    </select>
    <select name="age_range" required style="width:100%;padding:10px;margin-bottom:15px;background:#1e1e2e;border:1px solid #2a2a3a;border-radius:6px;color:#e0e0e0;">
      <?php foreach(AGE_RANGES as $k => $v): ?><option value="<?=h($k)?>"><?=h($v)?></option><?php endforeach; ?>
    </select>
    <button type="submit" style="width:100%;padding:12px;background:#77b8f0;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;">Register</button>
  </form>
  <p style="text-align:center;margin-top:15px;color:#999;">
    <a href="?page=login" style="color:#77b8f0;">Login</a>
  </p>
</div>
