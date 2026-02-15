<?php
Auth::requireAdmin();
$penalties=DB::q("SELECT p.*,u.username FROM penalties p JOIN users u ON p.user_uuid=u.uuid WHERE p.is_active=1 ORDER BY p.issued_at DESC")->fetchAll();
?>
<div class="admin-header"><h1>Penalty Management</h1><p>Active penalties</p></div>
<div class="info-card"><h2><?=count($penalties)?> Active Penalties</h2>
<?php if(empty($penalties)):?>
<div style="text-align:center;padding:3rem;color:var(--text-tertiary)"><p>No active penalties</p></div>
<?php else:?>
<div class="admin-table-wrapper"><table class="admin-table table"><thead><tr><th>User</th><th>Level</th><th>Reason</th><th>Issued By</th><th>Issued</th><th>Expires</th><th>Actions</th></tr></thead><tbody>
<?php foreach($penalties as $p):?>
<tr><td><?=H::s($p['username'])?></td>
<td><span class="tier-badge" style="background:var(--error)">P<?=$p['penalty_level']?></span></td>
<td><?=H::s($p['reason'])?></td><td><?=H::s($p['issued_by'])?></td>
<td><?=H::timeAgo($p['issued_at'])?></td><td><?=$p['expires_at']?H::datetime($p['expires_at']):'Never'?></td>
<td><form method="POST" style="display:inline"><input type="hidden" name="action" value="revoke_penalty"><input type="hidden" name="uuid" value="<?=$p['user_uuid']?>"><button type="submit" class="btn-success" style="padding:0.5rem">Revoke</button></form></td></tr>
<?php endforeach;?>
</tbody></table></div>
<?php endif;?>
</div>
