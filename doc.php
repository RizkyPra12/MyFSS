<?php
/**
 * RyPanel v3.1 — Private Document Handler
 * Serves user documents securely after validating session ownership.
 * 
 * Usage:
 *   doc.php?id={doc_id}        → Preview/inline for images, PDF, text
 *   doc.php?id={doc_id}&dl=1   → Force download
 */

require_once 'config.php';
require_once 'includes/Backend.php';

session_name(SESSION_NAME);
session_start();

// Require authentication
if (!Auth::check()) {
    http_response_code(401);
    die('Unauthorized — please log in');
}

// Validate doc ID parameter
$id = $_GET['id'] ?? '';
if (!$id) {
    http_response_code(400);
    die('Missing file ID');
}

// Check if force download requested
$forceDownload = isset($_GET['dl']) && $_GET['dl'] == '1';

// Serve file (Doc::serve validates ownership internally)
Doc::serve($id, Auth::uid(), $forceDownload);
