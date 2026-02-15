<?php
Auth::requireAdmin();

// Handle repository switch
$repo = $_GET['repo'] ?? 'public';
if (!in_array($repo, ['public', 'private'])) $repo = 'public';

// Handle delete
if (isset($_GET['delete'])) {
    $result = Upload::delete($_GET['delete'], Auth::uid());
    $_SESSION[$result['ok']?'success':'error'] = $result['ok'] ? 'File deleted' : $result['msg'];
    header('Location: admin.php?page=uploads&repo='.$repo);
    exit;
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $result = Upload::save($_FILES['file'], $repo, Auth::uid(), Auth::username(), 'contributor');
    
    if ($result['ok']) {
        $_SESSION['success'] = 'File uploaded successfully!';
    } else {
        $_SESSION['error'] = $result['msg'];
    }
    
    header('Location: admin.php?page=uploads&repo='.$repo);
    exit;
}

$uploads = Upload::list($repo);
?>

<div class="admin-header">
    <h1><?= t('upload_management') ?></h1>
    <p><?= t('manage_all_uploads') ?></p>
</div>

<!-- Repository Switcher -->
<div style="display:flex;gap:var(--space-sm);margin-bottom:var(--space-lg)">
    <a href="?page=uploads&repo=public" class="btn-<?= $repo==='public'?'primary':'secondary' ?>">
        📁 <?= t('public_repository') ?>
    </a>
    <a href="?page=uploads&repo=private" class="btn-<?= $repo==='private'?'primary':'secondary' ?>">
        🔒 <?= t('private_repository') ?>
    </a>
</div>

<!-- Upload Form -->
<div class="info-card">
    <h2><?= t('upload_to') ?> <?= strtoupper($repo) ?></h2>
    
    <?php if($repo === 'private'): ?>
    <div class="alert info" style="margin-bottom:var(--space-md)">
        <strong><?= t('private_repository_note') ?>:</strong>
        <?= t('only_admins_can_see') ?>
    </div>
    <?php else: ?>
    <div class="alert warning" style="margin-bottom:var(--space-md)">
        <strong><?= t('public_repository_note') ?>:</strong>
        <?= t('all_users_can_see') ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label"><?= t('select_file') ?></label>
            <input type="file" name="file" required accept="*/*">
            <small style="color:var(--text-tertiary);display:block;margin-top:0.5rem">
                ✓ <?= t('unlimited_admin') ?> • <?= t('no_cost') ?>
            </small>
        </div>
        
        <button type="submit" class="btn-primary">
            <img src="https://api.iconify.design/mdi/upload.svg" class="icon" alt="">
            <?= t('upload_file') ?>
        </button>
    </form>
</div>

<!-- Files List -->
<div class="info-card">
    <h2><?= count($uploads) ?> <?= t('files_in') ?> <?= strtoupper($repo) ?></h2>
    
    <?php if(empty($uploads)): ?>
        <div style="text-align:center;padding:3rem;color:var(--text-tertiary)">
            <img src="https://api.iconify.design/mdi/folder-open-outline.svg?color=%2394a3b8" style="width:80px;height:80px" alt="">
            <p style="margin-top:1rem"><?= t('no_files') ?></p>
        </div>
    <?php else: ?>
        <div class="admin-table-wrapper">
            <table class="admin-table table">
                <thead>
                    <tr>
                        <th><?= t('file_id') ?></th>
                        <th><?= t('filename') ?></th>
                        <th><?= t('original_name') ?></th>
                        <th><?= t('size') ?></th>
                        <th><?= t('type') ?></th>
                        <th><?= t('uploaded_by') ?></th>
                        <th><?= t('date') ?></th>
                        <th><?= t('time') ?></th>
                        <th><?= t('cost') ?></th>
                        <th><?= t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($uploads as $f): ?>
                    <tr>
                        <td><code><?= H::s($f['file_id']) ?></code></td>
                        <td><strong><?= H::s($f['filename']) ?></strong></td>
                        <td><?= H::s($f['original_filename']) ?></td>
                        <td><?= H::formatBytes($f['file_size']) ?></td>
                        <td><span class="tier-badge" style="background:var(--info)"><?= H::s($f['file_extension']) ?></span></td>
                        <td><?= H::s($f['uploaded_by_username']) ?></td>
                        <td><?= H::date($f['upload_date']) ?></td>
                        <td><?= date('H:i', strtotime($f['upload_time'])) ?></td>
                        <td><?= $f['credits_charged'] ?> pts</td>
                        <td>
                            <a href="<?= H::s($f['public_url']) ?>" target="_blank" class="btn-secondary" style="padding:0.5rem;margin-right:0.5rem">
                                <?= t('view') ?>
                            </a>
                            <a href="?page=uploads&repo=<?= $repo ?>&delete=<?= H::s($f['file_id']) ?>" 
                               onclick="return confirm('Delete this file?')" 
                               class="btn-danger" style="padding:0.5rem">
                                <?= t('delete') ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
