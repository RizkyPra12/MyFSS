<?php
Auth::requireAdmin();
if($_SERVER['REQUEST_METHOD']==='POST'&&$_POST['action']==='create_vote'){
$data=['title'=>$_POST['title'],'description'=>$_POST['description'],'type'=>$_POST['type'],'options'=>array_filter(array_map('trim',explode("\n",$_POST['options']))),'start_date'=>$_POST['start_date'],'end_date'=>$_POST['end_date']];
$r=Voting::create($data,Auth::username());
$_SESSION[$r['ok']?'success':'error']=$r['ok']?'Vote created':'Failed to create';
header('Location: admin.php?page=voting');exit;
}
$votes=Voting::all(false);
?>
<div class="admin-header"><h1>Voting Management</h1></div>
<div class="info-card"><h2>Create New Vote</h2>
<form method="POST"><input type="hidden" name="action" value="create_vote">
<div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" required></div>
<div class="form-group"><label class="form-label">Description</label><textarea name="description" rows="2"></textarea></div>
<div class="info-grid">
<div class="form-group"><label class="form-label">Type *</label><select name="type" required><option value="vote">Vote</option><option value="election">Election</option><option value="polling">Polling</option></select></div>
<div class="form-group"><label class="form-label">Start Date *</label><input type="datetime-local" name="start_date" required></div>
<div class="form-group"><label class="form-label">End Date *</label><input type="datetime-local" name="end_date" required></div>
</div>
<div class="form-group"><label class="form-label">Options (one per line) *</label><textarea name="options" rows="4" required placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea></div>
<button type="submit" class="btn-primary">Create Vote</button>
</form></div>
<div class="info-card"><h2><?=count($votes)?> Total Votes</h2>
<div class="admin-table-wrapper"><table class="admin-table table"><thead><tr><th>ID</th><th>Title</th><th>Type</th><th>Starts</th><th>Ends</th><th>Status</th><th>Results</th></tr></thead><tbody>
<?php foreach($votes as $v):$results=Voting::getResults($v['vote_id']);$total=array_sum(array_column($results,'count'));?>
<tr><td><?=H::s($v['vote_id'])?></td><td><?=H::s($v['vote_title'])?></td>
<td><span class="vote-type-badge <?=$v['vote_type']?>"><?=strtoupper($v['vote_type'])?></span></td>
<td><?=H::datetime($v['start_date'])?></td><td><?=H::datetime($v['end_date'])?></td>
<td><?=Voting::isActive($v)?'<span class="tier-badge" style="background:var(--success)">Active</span>':'<span class="tier-badge" style="background:var(--text-tertiary)">Ended</span>'?></td>
<td><?=$total?> votes</td></tr>
<?php endforeach;?>
</tbody></table></div></div>
