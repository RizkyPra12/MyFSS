<?php
Auth::requireAdmin();
$stats=Analytics::stats();
?>
<div class="admin-header"><h1>Analytics</h1><p>Last 30 days</p></div>
<div class="stats-grid">
<div class="stat-card"><div class="stat-value"><?=H::num($stats['total'])?></div><div class="stat-label">Total Activities</div></div>
</div>
<div class="info-grid">
<div class="info-card"><h2>Devices</h2>
<?php foreach($stats['devices'] as $d):?>
<div class="info-item"><div class="info-label"><?=ucfirst($d['device_category'])?></div><div class="info-value"><?=$d['c']?></div></div>
<?php endforeach;?>
</div>
<div class="info-card"><h2>Top Activities</h2>
<?php foreach($stats['activities'] as $a):?>
<div class="info-item"><div class="info-label"><?=$a['activity_type']?></div><div class="info-value"><?=$a['c']?></div></div>
<?php endforeach;?>
</div>
</div>
