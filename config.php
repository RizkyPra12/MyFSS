<?php
/**
 * MyFSS Configuration
 * CHANGE THESE VALUES FOR YOUR SERVER
 */

// === DATABASE (CHANGE THESE) ===
define('DB_HOST', 'localhost');
define('DB_NAME', 'fictional_country');
define('DB_USER', 'root');
define('DB_PASS', '0909');

// === SITE SETTINGS ===
define('SITE_NAME', 'MyFSS');
define('SITE_TAGLINE', 'Panel Resmi FSS');
define('SITE_VERSION', '1.0.1');
define('SESSION_NAME', 'MYFSS_SESSION');

// === PATHS ===
define('BASE_PATH', __DIR__);

// === THEME ===
define('THEME_PRIMARY', '#77b8f0');

// === SETTINGS ===
define('FCD_STARTING_BALANCE', 100);

// === AGE RANGES ===
define('AGE_RANGES', [
    '13-17' => '13-17',
    '18-25' => '18-25',
    '26-35' => '26-35',
    '36-45' => '36-45',
    '46-55' => '46-55',
    '56+' => '56+'
]);

// === ICONS (Optional - will use emoji if missing) ===
define('ICONS', [
    'wallet' => 'assets/icons/wallet.webp',
    'upload' => 'assets/icons/upload.webp',
    'doc' => 'assets/icons/upload.webp',
    'certs' => 'assets/icons/certs.webp',
    'vote' => 'assets/icons/vote.webp',
    'events' => 'assets/icons/events.webp',
    'settings' => 'assets/icons/settings.webp',
    'about' => 'assets/icons/about.webp',
    'admin' => 'assets/icons/admin.webp',
    'logout' => 'assets/icons/logout.webp'
]);

function getIcon($name) {
    if(isset(ICONS[$name]) && file_exists(ICONS[$name])) {
        return ICONS[$name];
    }
    return null;
}
