<?php
/**
 * MyFSS v1.0.1
 * Bulletproof index.php - No 500 errors
 */

// Step 1: Enable error display (comment out in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Step 2: Check if config exists
if(!file_exists(__DIR__ . '/config.php')) {
    die('<h1>Setup Required</h1><p>config.php not found. Please create it.</p>');
}

// Step 3: Require files with error handling
try {
    require_once __DIR__ . '/config.php';
} catch(Exception $e) {
    die('<h1>Config Error</h1><p>Error loading config.php: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

if(!file_exists(__DIR__ . '/includes/Backend.php')) {
    die('<h1>Setup Required</h1><p>includes/Backend.php not found.</p>');
}

try {
    require_once __DIR__ . '/includes/Backend.php';
} catch(Exception $e) {
    die('<h1>Backend Error</h1><p>Error loading Backend.php: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

if(!file_exists(__DIR__ . '/lang.php')) {
    die('<h1>Setup Required</h1><p>lang.php not found.</p>');
}

try {
    require_once __DIR__ . '/lang.php';
} catch(Exception $e) {
    die('<h1>Language Error</h1><p>Error loading lang.php: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

// Step 4: Start session
if(!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'MYFSS_SESSION');
}

session_name(SESSION_NAME);
session_start();

// Step 5: Helper functions
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function detectLogo() {
    $files = ['logo.png', 'logo.jpg', 'logo.svg', 'icon.png', 'icon.jpg'];
    foreach($files as $file) {
        if(file_exists(__DIR__ . '/' . $file)) {
            return $file;
        }
    }
    return null;
}

function icon($name, $emoji = '') {
    if(function_exists('getIcon')) {
        $path = getIcon($name);
        if($path && file_exists($path)) {
            return '<img src="' . h($path) . '" class="dash-btn-icon" alt="' . h($name) . '">';
        }
    }
    return '<span class="dash-btn-icon emoji">' . h($emoji) . '</span>';
}

// Step 6: Maintenance (safe mode)
try {
    if(class_exists('Credits')) Credits::refillAll();
    if(class_exists('Penalty')) Penalty::checkExpired();
    if(class_exists('Voting')) Voting::checkMissedElections();
} catch(Exception $e) {
    // Silent fail
}

// Step 7: Logout handler
if(isset($_GET['action']) && $_GET['action'] === 'logout') {
    if(class_exists('Auth')) {
        Auth::logout();
    } else {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// Step 8: Determine page
$page = $_GET['page'] ?? 'login';

if(class_exists('Auth') && Auth::check()) {
    if(in_array($page, ['login', 'register'])) {
        $page = 'dashboard';
    }
} else {
    if(!in_array($page, ['login', 'register'])) {
        $page = 'login';
    }
}

// Step 9: Admin check
$adminPages = ['admin-dashboard', 'admin-members', 'admin-find', 'admin-events', 'admin-votes'];
if(in_array($page, $adminPages)) {
    if(!class_exists('Auth') || !Auth::check() || !Auth::isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Step 10: Find page file
$pageFile = null;
if(strpos($page, 'admin-') === 0) {
    $adminPage = str_replace('admin-', '', $page);
    $pageFile = __DIR__ . '/pages/admin/' . $adminPage . '.php';
} else {
    $pageFile = __DIR__ . '/pages/' . $page . '.php';
}

// Step 11: Fallback if page not found
if(!file_exists($pageFile)) {
    $page = class_exists('Auth') && Auth::check() ? 'dashboard' : 'login';
    $pageFile = __DIR__ . '/pages/' . $page . '.php';
}

// Step 12: Get user data
$U = null;
$logo = detectLogo();

if(class_exists('Auth') && Auth::check()) {
    try {
        if(class_exists('User')) {
            $U = User::get();
        }
    } catch(Exception $e) {
        // If can't get user, logout
        Auth::logout();
    }
}

// Step 13: Output HTML
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="<?php echo defined('THEME_PRIMARY') ? THEME_PRIMARY : '#77b8f0'; ?>">
<title><?php echo defined('SITE_NAME') ? SITE_NAME : 'MyFSS'; ?> - <?php echo ucfirst($page); ?></title>
<link rel="stylesheet" href="assets/css/main.css">
<style>
/* Fallback CSS if main.css missing */
body{font-family:sans-serif;background:#0a0a0f;color:#e0e0e0;margin:0;padding:20px;}
.content{max-width:600px;margin:0 auto;}
.alert{padding:12px;border-radius:8px;margin:10px 0;}
.alert-success{background:rgba(0,221,102,0.1);border:1px solid #00dd66;color:#00dd66;}
.alert-error{background:rgba(255,51,102,0.1);border:1px solid #ff3366;color:#ff3366;}
</style>
</head>
<body>
<div id="app">
<main class="content">
<?php
// Display messages
if(isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . h($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}

if(isset($_SESSION['error'])) {
    echo '<div class="alert alert-error">' . h($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}

// Load page
if(file_exists($pageFile)) {
    try {
        include $pageFile;
    } catch(Exception $e) {
        echo '<div class="alert alert-error">Error loading page.</div>';
        if(ini_get('display_errors')) {
            echo '<pre>' . h($e->getMessage()) . '</pre>';
        }
    }
} else {
    echo '<div class="alert alert-error">Page file not found: ' . h(basename($pageFile)) . '</div>';
}
?>
</main>
</div>
</body>
</html>
