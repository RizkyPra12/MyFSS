<?php
if(!Auth::isAdmin()) die('Access denied');

$publicFiles = FileUpload::listAll();
$docs = DB::q("SELECT d.*, u.username FROM user_documents d JOIN users u ON d.user_uuid = u.uuid ORDER BY d.created_at DESC LIMIT 50")->fetchAll();
?>

<a href="?page=admin-dashboard" class="back-btn"><?=icon('back')?> Back</a>

<div class="card">
    <div class="card-header">Public Files (<?=count($publicFiles)?>)</div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:2px solid var(--border)">
                    <th style="text-align:left;padding:0.8rem">Filename</th>
                    <th style="text-align:left;padding:0.8rem">Uploader</th>
                    <th style="text-align:right;padding:0.8rem">Size</th>
                    <th style="text-align:left;padding:0.8rem">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($publicFiles as $f): ?>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:0.8rem"><?=h($f['original_name'])?></td>
                    <td style="padding:0.8rem"><?=h($f['uploader'])?></td>
                    <td style="padding:0.8rem;text-align:right;font-family:monospace"><?=H::formatBytes($f['size'])?></td>
                    <td style="padding:0.8rem"><?=H::timeAgo(date('Y-m-d H:i:s', $f['uploaded_at']))?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">Private Documents (<?=count($docs)?>)</div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:2px solid var(--border)">
                    <th style="text-align:left;padding:0.8rem">Filename</th>
                    <th style="text-align:left;padding:0.8rem">Owner</th>
                    <th style="text-align:right;padding:0.8rem">Size</th>
                    <th style="text-align:left;padding:0.8rem">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($docs as $d): ?>
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:0.8rem"><?=h($d['original_filename'])?></td>
                    <td style="padding:0.8rem"><?=h($d['username'])?></td>
                    <td style="padding:0.8rem;text-align:right;font-family:monospace"><?=H::formatBytes($d['file_size'])?></td>
                    <td style="padding:0.8rem"><?=date('M j, Y', strtotime($d['upload_date']))?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
