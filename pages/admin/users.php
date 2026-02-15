<?php
if(!Auth::isAdmin()) die('Access denied');

if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $uuid = $_POST['uuid'] ?? '';
    $updates = [
        'tier' => $_POST['tier'] ?? 'free',
        'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
        'fcd_balance' => (float)($_POST['fcd_balance'] ?? 0),
    ];
    User::update($uuid, $updates);
    $_SESSION['success'] = 'User updated';
    header('Location: ?page=admin-users');
    exit;
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Optimized query with pagination
if($search) {
    $countQuery = "SELECT COUNT(*) FROM users WHERE username LIKE ? OR country_name LIKE ?";
    $total = DB::q($countQuery, ["%$search%", "%$search%"])->fetchColumn();
    $users = DB::q("SELECT uuid, username, country_name, tier, fcd_balance, is_admin, created_at FROM users WHERE username LIKE ? OR country_name LIKE ? ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}", ["%$search%", "%$search%"])->fetchAll();
} else {
    $total = DB::q("SELECT COUNT(*) FROM users")->fetchColumn();
    $users = DB::q("SELECT uuid, username, country_name, tier, fcd_balance, is_admin, created_at FROM users ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}")->fetchAll();
}

$totalPages = ceil($total / $perPage);
?>

<a href="?page=admin-dashboard" class="back-btn"><?=icon('back')?> Back</a>

<div class="card">
    <div class="card-header">User Management (<?=number_format($total)?>)</div>
    <form method="GET" style="display:flex;gap:0.5rem;margin-top:1rem">
        <input type="hidden" name="page" value="admin-users">
        <input type="text" name="search" placeholder="Search by username or country..." value="<?=h($search)?>" style="flex:1">
        <button type="submit" class="btn">Search</button>
        <?php if($search): ?>
        <a href="?page=admin-users" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div style="overflow-x:auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Country</th>
                    <th>Tier</th>
                    <th style="text-align:right">FCD Balance</th>
                    <th style="text-align:center">Admin</th>
                    <th style="text-align:center">Joined</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><strong><?=h($u['username'])?></strong></td>
                    <td><?=h($u['country_name'])?></td>
                    <td><span class="tier-badge tier-<?=h($u['tier'])?>"><?=h($u['tier'])?></span></td>
                    <td style="text-align:right;font-family:monospace"><?=number_format($u['fcd_balance'],2)?></td>
                    <td style="text-align:center"><?=$u['is_admin']?'<span style="color:var(--success)">✓</span>':''?></td>
                    <td style="text-align:center;font-size:0.85rem;color:var(--text-muted)"><?=H::timeAgo($u['created_at'])?></td>
                    <td style="text-align:center">
                        <button onclick="editUser('<?=h($u['uuid'])?>', '<?=h($u['username'])?>', '<?=h($u['tier'])?>', <?=$u['fcd_balance']?>, <?=$u['is_admin']?>)" class="btn-icon" title="Edit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php if($page > 1): ?>
        <a href="?page=admin-users&p=<?=$page-1?><?=$search?"&search=".urlencode($search):''?>" class="btn btn-sm btn-secondary">← Previous</a>
        <?php endif; ?>
        <span style="padding:0.5rem 1rem;color:var(--text-secondary)">Page <?=$page?> of <?=$totalPages?></span>
        <?php if($page < $totalPages): ?>
        <a href="?page=admin-users&p=<?=$page+1?><?=$search?"&search=".urlencode($search):''?>" class="btn btn-sm btn-secondary">Next →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Edit User</div>
            <button onclick="closeModal()" class="modal-close">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="uuid" id="editUuid">
            <div class="modal-body">
                <div class="fg">
                    <label>Username</label>
                    <input type="text" id="editUsername" disabled style="opacity:0.6;cursor:not-allowed">
                </div>
                <div class="fg">
                    <label>Tier</label>
                    <select name="tier" id="editTier">
                        <option value="free">Free</option>
                        <option value="tier1">Tier 1</option>
                        <option value="tier2">Tier 2</option>
                        <option value="tier3">Tier 3</option>
                        <option value="special">Special</option>
                        <option value="contributor">Contributor</option>
                    </select>
                </div>
                <div class="fg">
                    <label>FCD Balance</label>
                    <input type="number" name="fcd_balance" id="editBalance" step="0.01" min="0">
                </div>
                <div class="fg">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
                        <input type="checkbox" name="is_admin" id="editAdmin">
                        <span>Administrator Access</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table thead {
    background: var(--bg-input);
}

.admin-table th {
    text-align: left;
    padding: 1rem;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
}

.admin-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
}

.admin-table tbody tr {
    transition: background 0.2s;
}

.admin-table tbody tr:hover {
    background: var(--bg-hover);
}

.tier-badge {
    display: inline-block;
    padding: 0.25rem 0.6rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.tier-free { background: rgba(153,153,153,0.1); color: var(--text-muted); }
.tier-tier1 { background: rgba(119,184,240,0.1); color: var(--blue); }
.tier-tier2 { background: rgba(0,221,102,0.1); color: var(--success); }
.tier-tier3 { background: rgba(255,170,0,0.1); color: var(--warning); }
.tier-special { background: rgba(255,51,102,0.1); color: var(--error); }
.tier-contributor { background: rgba(138,43,226,0.1); color: #8a2be2; }

.btn-icon {
    background: none;
    border: none;
    color: var(--blue);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: rgba(119,184,240,0.1);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}
</style>

<script>
function editUser(uuid, username, tier, balance, isAdmin) {
    document.getElementById('editUuid').value = uuid;
    document.getElementById('editUsername').value = username;
    document.getElementById('editTier').value = tier;
    document.getElementById('editBalance').value = balance;
    document.getElementById('editAdmin').checked = isAdmin == 1;
    document.getElementById('editModal').classList.add('active');
}

function closeModal() {
    document.getElementById('editModal').classList.remove('active');
}
</script>
