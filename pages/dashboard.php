<?php
$fcd = Wallet::balance(Auth::uid());
?>
<div style="background:linear-gradient(135deg,#77b8f0,#5a9dd8);padding:30px;border-radius:12px;text-align:center;margin-bottom:20px;">
  <?php if($logo): ?>
  <img src="<?=h($logo)?>" style="max-width:100px;margin-bottom:15px;">
  <?php endif; ?>
  <?php if(!empty($U['flag_url'])): ?>
  <div style="display:inline-block;padding:4px;background:#14141f;border:2px solid #77b8f0;border-radius:8px;margin:10px 0;">
    <img src="<?=h($U['flag_url'])?>" style="width:80px;aspect-ratio:3/2;border-radius:4px;" onerror="this.style.display='none'">
  </div>
  <?php endif; ?>
  <div style="color:#fff;font-size:18px;">Welcome, @<?=h($U['username'])?></div>
</div>

<div style="background:#14141f;border:1px solid #77b8f0;padding:20px;border-radius:12px;text-align:center;margin-bottom:20px;">
  <div style="font-size:32px;font-weight:900;color:#77b8f0;"><?=number_format($fcd,0)?> FCD</div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:10px;">
  <a href="?page=wallet" style="background:#14141f;border:1px solid #2a2a3a;padding:20px 10px;border-radius:12px;text-align:center;text-decoration:none;color:#e0e0e0;">💸<br>Wallet</a>
  <a href="?page=about" style="background:#14141f;border:1px solid #2a2a3a;padding:20px 10px;border-radius:12px;text-align:center;text-decoration:none;color:#e0e0e0;">ℹ️<br>About</a>
  <a href="?page=settings" style="background:#14141f;border:1px solid #2a2a3a;padding:20px 10px;border-radius:12px;text-align:center;text-decoration:none;color:#e0e0e0;">⚙️<br>Settings</a>
  <a href="?action=logout" style="grid-column:span 2;background:#14141f;border:1px solid #2a2a3a;padding:20px;border-radius:12px;text-align:center;text-decoration:none;color:#e0e0e0;">🚪 Logout</a>
</div>
