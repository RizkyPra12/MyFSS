<?php
require_once 'config.php';
require_once 'includes/Backend.php';

session_name(SESSION_NAME);
session_start();

if(!Auth::check()) {
    http_response_code(403);
    die('Access denied');
}

$doc_id = $_GET['id'] ?? '';
$doc = DB::q("SELECT * FROM user_documents WHERE doc_id = ? AND user_uuid = ?", [$doc_id, Auth::uid()])->fetch();

if(!$doc) {
    http_response_code(404);
    die('Document not found');
}

$filepath = DOC_PATH . '/' . Auth::uid() . '/' . $doc['stored_filename'];

if(!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
}

header('Content-Type: ' . $doc['mime_type']);
header('Content-Disposition: attachment; filename="' . $doc['original_filename'] . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
