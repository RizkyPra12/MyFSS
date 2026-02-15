<?php
// This script generates all page files with full functionality
// Run: php generate_all_pages.php

$pages_dir = __DIR__ . '/pages';

$pages = [
    'events.php' => file_get_contents(__DIR__ . '/pages_templates/events.txt'),
    'voting.php' => file_get_contents(__DIR__ . '/pages_templates/voting.txt'),
    'upload.php' => file_get_contents(__DIR__ . '/pages_templates/upload.txt'),
    'doc.php' => file_get_contents(__DIR__ . '/pages_templates/doc.txt'),
    'certs.php' => file_get_contents(__DIR__ . '/pages_templates/certs.txt'),
    'settings.php' => file_get_contents(__DIR__ . '/pages_templates/settings.txt'),
    'about.php' => file_get_contents(__DIR__ . '/pages_templates/about.txt'),
];

foreach($pages as $filename => $content) {
    file_put_contents($pages_dir . '/' . $filename, $content);
    echo "✓ Created $filename\n";
}

echo "\nAll pages created successfully!\n";
