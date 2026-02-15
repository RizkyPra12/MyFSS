<?php
Auth::requireAdmin();
$user=null;$results=[];
if(isset($_GET['u'])){$user=DB::q("SELECT * FROM users WHERE username=?",[$_GET['u']])->fetch();}
if($_SERVER['REQUEST_METHOD']==='POST'){
$a=$_POST['action']??'';
if($a==='search'){$results=User::search($_POST);}
if($a==='change_tier'){User::update($_POST['uuid'],['tier'=>$_POST['tier']]);$_SESSION['success']='Tier updated';$user=User::get($_POST['uuid']);}
if($a==='give_penalty'){$r=Penalty::give($_POST['uuid'],$_POST['level'],$_POST['reason'],Auth::username(),$_POST['days']??null);$_SESSION[$r['ok']?'success':'error']=$r['ok']?'Penalty given':$r['msg'];$user=User::get($_POST['uuid']);}
if($a==='revoke_penalty'){Penalty::revoke($_POST['uuid'],Auth::username());$_SESSION['success']='Penalty revoked';$user=User::get($_POST['uuid']);}
if($a==='adjust_credits'){User::update($_POST['uuid'],['credits'=>$_POST['credits']]);$_SESSION['success']='Credits adjusted';$user=User::get($_POST['uuid']);}
}
?>
<div class="admin-header"><h1>Find User</h1></div>
<div class="info-card"><h2>Search Users</h2>
<form method="POST" class="find-user-form" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-md)">
<input type="hidden" name="action" value="search">
<div class="form-group" style="margin:0"><input type="text" name="username" placeholder="Username"></div>
<div class="form-group" style="margin:0"><input type="text" name="country" placeholder="Country"></div>
<div class="form-group" style="margin:0"><input type="text" name="email" placeholder="Email"></div>
<div class="form-group" style="margin:0"><input type="text" name="phone" placeholder="Phone"></div>
<button type="submit" class="btn-primary">Search</button>
</form></div>
<?php if($results):?>
<div class="info-card"><h2><?=count($results)?> Results</h2>
<div class="table-responsive"><table class="table"><thead><tr><th>Username</th><th>Country</th><th>Tier</th><th>Actions</th></tr></thead><tbody>
<?php foreach($results as $r):?>
<tr><td><?=H::s($r['username'])?></td><td><?=H::s($r['country_name'])?></td><td><?=$r['tier']?></td>
<td><a href="?page=find-user&u=<?=urlencode($r['username'])?>" class="btn-secondary" style="padding:0.5rem">View</a></td></tr>
<?php endforeach;?>
</tbody></table></div></div>
<?php endif;?>
<?php if($user):$tier=TIER_CONFIG[$user['tier']]??TIER_CONFIG['free'];$penalty=Penalty::getActive($user['uuid']);?>
<div class="info-card"><h2>User: <?=H::s($user['username'])?></h2>
<div class="info-grid">
<div class="info-item"><div class="info-label">UUID</div><div class="info-value"><code><?=H::s($user['uuid'])?></code></div></div>
<div class="info-item"><div class="info-label">Country</div><div class="info-value"><?=H::s($user['country_name'])?></div></div>
<div class="info-item"><div class="info-label">Tier</div><div class="info-value"><span class="tier-badge" style="background:<?=$tier['color']?>"><?=$tier['name']?></span></div></div>
<div class="info-item"><div class="info-label">Credits</div><div class="info-value"><?=User::isUnlimited($user)?'∞':H::num($user['credits'])?></div></div>
<div class="info-item"><div class="info-label">Penalty</div><div class="info-value"><span class="tier-badge" style="background:<?=$user['current_penalty']>0?'var(--error)':'var(--success)'?>">P<?=$user['current_penalty']?></span></div></div>
<div class="info-item"><div class="info-label">Joined</div><div class="info-value"><?=H::datetime($user['created_at'])?></div></div>
</div></div>
<div class="info-grid">
<div class="info-card"><h2>Change Tier</h2>
<form method="POST"><input type="hidden" name="action" value="change_tier"><input type="hidden" name="uuid" value="<?=$user['uuid']?>">
<select name="tier" required>
<?php foreach(TIER_CONFIG as $k=>$v):?>
<option value="<?=$k?>" <?=$k===$user['tier']?'selected':''?>><?=$v['name']?></option>
<?php endforeach;?>
</select>
<button type="submit" class="btn-primary" style="margin-top:var(--space-md)">Update Tier</button>
</form></div>
<div class="info-card"><h2>Adjust Credits</h2>
<form method="POST"><input type="hidden" name="action" value="adjust_credits"><input type="hidden" name="uuid" value="<?=$user['uuid']?>">
<input type="number" name="credits" value="<?=$user['credits']?>" required>
<button type="submit" class="btn-primary" style="margin-top:var(--space-md)">Adjust Credits</button>
</form></div>
</div>
<div class="info-card"><h2>Penalty Management</h2>
<?php if($penalty):?>
<div class="alert error" style="margin-bottom:var(--space-md)">
<strong>Active Penalty: P<?=$penalty['penalty_level']?></strong><br>
Reason: <?=H::s($penalty['reason'])?><br>
Issued: <?=H::datetime($penalty['issued_at'])?><br>
<?php if($penalty['expires_at']):?>Expires: <?=H::datetime($penalty['expires_at'])?><?php endif;?>
</div>
<form method="POST"><input type="hidden" name="action" value="revoke_penalty"><input type="hidden" name="uuid" value="<?=$user['uuid']?>">
<button type="submit" class="btn-success">Revoke Penalty</button>
</form>
<?php else:?>
<form method="POST"><input type="hidden" name="action" value="give_penalty"><input type="hidden" name="uuid" value="<?=$user['uuid']?>">
<div class="info-grid">
<div class="form-group"><label class="form-label">Level *</label><select name="level" required>
<option value="1">P1 - Warning</option><option value="2">P2 - Light Restriction</option>
<option value="3">P3 - Moderate Restriction</option><option value="4">P4 - Account Frozen</option>
<option value="5">P5 - Account Banned</option>
</select></div>
<div class="form-group"><label class="form-label">Duration (days, 0=permanent)</label><input type="number" name="days" value="0" min="0"></div>
</div>
<div class="form-group"><label class="form-label">Reason *</label><textarea name="reason" required rows="2"></textarea></div>
<button type="submit" class="btn-danger">Give Penalty</button>
</form>
<?php endif;?>
</div>
<?php endif;?>
