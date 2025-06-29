<?php

$inputFile = 'pasted.txt';
$outputBaseDir = 'output';

// Vérification de la présence du fichier
if (!file_exists($inputFile)) {
    die("Erreur : Le fichier '$inputFile' n'existe pas dans le dossier courant : " . getcwd() . "\n");
}

if (!is_dir($outputBaseDir)) {
    mkdir($outputBaseDir, 0777, true);
}

// Lecture du fichier entier
$content = file_get_contents($inputFile);

// Extraction de toutes les classes de service PHP (namespace + class)
preg_match_all(
// Capture tout le code d'une classe avec son namespace, même sur plusieurs lignes
    '/namespace\s+([a-zA-Z0-9_\\\\]+)\s*;\s*([\s\S]*?)(class\s+([A-Za-z0-9_]+)\s+implements\s+[A-Za-z0-9_\\\\]+[^{]*\{[\s\S]+?\n\})/m',
    $content,
    $matches,
    PREG_SET_ORDER
);

$fileCount = 0;

foreach ($matches as $match) {
    $namespace = trim($match[1]);
    $classBlock = $match[3];

    // Récupérer le nom de la classe
    if (!preg_match('/class\s+([A-Za-z0-9_]+)/', $classBlock, $classNameMatch)) {
        echo "Bloc ignoré : nom de classe introuvable.\n";
        continue;
    }
    $className = $classNameMatch[1];

    // Construction du chemin de sortie
    $dir = $outputBaseDir . '/' . str_replace('\\', '/', $namespace);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $filePath = $dir . '/' . $className . '.php';

    // Reconstituer le code complet
    $finalCode = "<?php\n\nnamespace $namespace;\n\n" . $classBlock;

    file_put_contents($filePath, $finalCode);
    echo "Créé : $filePath\n";
    $fileCount++;
}

echo "Terminé. $fileCount services extraits dans '$outputBaseDir'.\n";