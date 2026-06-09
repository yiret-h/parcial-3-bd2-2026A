<?php
$dir = __DIR__ . '/../'; // Pointing to app/
$files = glob($dir . '*.php');

$emojis = [
    '🐾' => '&#128062;',
    '🐶' => '&#128054;',
    '🐱' => '&#128049;',
    '🩺' => '&#129658;',
    '💊' => '&#128138;',
    '💉' => '&#128137;',
    '📅' => '&#128197;',
    '🏠' => '&#127968;',
    '📊' => '&#128202;',
    '🚪' => '&#128682;',
    '🧪' => '&#129514;',
    '👤' => '&#128100;',
    '🔍' => '&#128269;',
    '💾' => '&#128190;',
    '✏️' => '&#9999;',
    '🎂' => '&#127874;',
    '📋' => '&#128203;',
    '📄' => '&#128196;',
    '⏳' => '&#9203;',
    '✅' => '&#9989;',
    '🚨' => '&#128680;',
    '📦' => '&#128230;',
    '⚠️' => '&#9888;'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Update CSS links
    $content = preg_replace('/huellitas-(shared|layout)\.css(\?v=[0-9]+)?/', 'huellitas-$1.css?v=4', $content);
    
    // Replace Emojis
    foreach ($emojis as $emoji => $code) {
        $content = str_replace($emoji, $code, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Updated: " . basename($file) . "\n";
    }
}
echo "Done.\n";
