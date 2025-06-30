<?php
/**
 * Script de scan frontend/src et public/assets
 * Liste tous les fichiers utiles dans liste_fichiers_frontend_assets.txt
 * À placer et exécuter à la racine du projet
 */

$frontendPath = __DIR__ . '/src/frontend';
$assetsPath = __DIR__ . '/public/assets';
$outputFile = __DIR__ . '/liste_fichiers_frontend_assets.txt';

function scanFiles($dir, $allowedExtensions = null) {
    $filesList = [];
    if (!is_dir($dir)) return $filesList;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (is_null($allowedExtensions) || in_array($ext, $allowedExtensions)) {
                $filesList[] = str_replace('\\', '/', substr($file->getPathname(), strlen(__DIR__) + 1));
            }
        }
    }
    sort($filesList);
    return $filesList;
}

$output = "";

// === FRONTEND SRC ===
$output .= "=== FRONTEND (src) ===\n\n";
$frontendFiles = scanFiles($frontendPath, ['php', 'html', 'js', 'vue', 'ts']);

$modules = [];
$pages = [];

foreach ($frontendFiles as $file) {
    $relative = substr($file, strlen('frontend/src/'));
    $parts = explode('/', $relative);
    if (count($parts) > 1) {
        $modules[$parts[0]][] = $relative;
    } else {
        $pages[] = $relative;
    }
}

$output .= "== Modules ==\n";
foreach ($modules as $module => $moduleFiles) {
    $output .= "- $module\n";
    foreach ($moduleFiles as $mf) {
        $output .= "    • $mf\n";
    }
}
$output .= "\n== Pages à la racine ==\n";
foreach ($pages as $page) {
    $output .= "- $page\n";
}

// === PUBLIC ASSETS ===
$output .= "\n=== PUBLIC ASSETS ===\n\n";
$assetFiles = scanFiles($assetsPath);

$css = [];
$js = [];
$images = [];
$others = [];

foreach ($assetFiles as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($ext === 'css') {
        $css[] = $file;
    } elseif ($ext === 'js') {
        $js[] = $file;
    } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
        $images[] = $file;
    } else {
        $others[] = $file;
    }
}

$output .= "== CSS ==\n";
foreach ($css as $c) {
    $output .= "- $c\n";
}
$output .= "\n== JS ==\n";
foreach ($js as $j) {
    $output .= "- $j\n";
}
$output .= "\n== Images ==\n";
foreach ($images as $i) {
    $output .= "- $i\n";
}
$output .= "\n== Autres ==\n";
foreach ($others as $o) {
    $output .= "- $o\n";
}

file_put_contents($outputFile, $output);

echo "✅ Liste générée dans $outputFile\n";