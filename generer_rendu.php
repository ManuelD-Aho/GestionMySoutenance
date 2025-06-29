<?php

/**
 * Script pour agréger le contenu de dossiers spécifiques et des fichiers à la racine
 * d'un projet dans des fichiers .txt uniques.
 */

// Chemin racine du projet
$projectRoot = __DIR__;

// Dossier de destination pour les fichiers .txt générés
$outputDir = $projectRoot . '/Rendu';

// =========================================================================
// CONFIGUREZ ICI LES CHEMINS À TRAITER
// Chaque chemin listé ici deviendra un fichier .txt dans le dossier "Rendu".
// =========================================================================
$pathsToProcess = [
    // Dossiers de premier niveau
    'Public',
    'routes',
    'docker',
    '.github',

    // Division du dossier 'src'
    'src/Config',
    'src/Frontend',

    // Division fine du dossier 'src/Backend'
    'src/Backend/Controller',
    'src/Backend/Exception',
    'src/Backend/Model',
    'src/Backend/Service',
    'src/Backend/Util',
];

// Dossiers à ignorer complètement lors du parcours des chemins ci-dessus
$ignoredFolders = [
    'vendor',
    'node_modules',
    '.git',
    'assets', // <-- Ajout ici pour ignorer Public/assets
    basename($outputDir) // Le dossier de rendu lui-même
];

// Crée le dossier de destination s'il n'existe pas
if (!is_dir($outputDir)) {
    if (!mkdir($outputDir, 0755, true)) {
        die("Erreur critique : Impossible de créer le répertoire de destination '$outputDir'.\n");
    }
    echo "Répertoire '$outputDir' créé avec succès.\n";
}

/**
 * Fonction récursive pour récupérer tout le contenu des fichiers d'un dossier.
 *
 * @param string $dir Le chemin du dossier à scanner.
 * @param string $rootPath Le chemin racine du projet pour créer des chemins relatifs.
 * @param array $ignoredFolders La liste des noms de dossiers à ignorer.
 * @return string Le contenu agrégé de tous les fichiers.
 */
function getAggregatedContent(string $dir, string $rootPath, array $ignoredFolders): string
{
    $content = '';

    if (!is_readable($dir)) {
        echo "Attention : Le chemin '$dir' n'est pas accessible en lecture.\n";
        return '';
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $pathname = $item->getPathname();

        foreach ($ignoredFolders as $ignored) {
            if (strpos($pathname, DIRECTORY_SEPARATOR . $ignored . DIRECTORY_SEPARATOR) !== false) {
                continue 2;
            }
        }

        if ($item->isFile() && $item->isReadable()) {
            $fileContent = file_get_contents($pathname);

            if ($fileContent === false) {
                echo "Attention : Impossible de lire le contenu du fichier '$pathname'.\n";
                continue;
            }

            $relativePath = str_replace($rootPath . DIRECTORY_SEPARATOR, '', $pathname);
            $content .= "\n/***********************************************************************************\n * Fichier: {$relativePath}\n ***********************************************************************************/\n\n" . $fileContent . "\n";
        }
    }
    return $content;
}

echo "Début de l'agrégation granulaire du contenu...\n\n";

// --- 1. TRAITEMENT DES DOSSIERS SPÉCIFIQUES ---
foreach ($pathsToProcess as $relativePath) {
    $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $relativePath;

    if (is_dir($fullPath)) {
        echo "Traitement du chemin : '$relativePath'...\n";
        $aggregatedContent = getAggregatedContent($fullPath, $projectRoot, $ignoredFolders);

        if (empty(trim($aggregatedContent))) {
            echo " -> Aucun contenu trouvé pour '$relativePath', fichier non généré.\n";
            continue;
        }

        $outputFileName = str_replace(['/', '\\'], '_', $relativePath) . '.txt';
        $outputFile = $outputDir . DIRECTORY_SEPARATOR . $outputFileName;

        if (file_put_contents($outputFile, $aggregatedContent) === false) {
            echo "Erreur : Impossible d'écrire dans le fichier '$outputFile'.\n";
        } else {
            echo " -> Fichier '$outputFileName' généré avec succès.\n";
        }
    } else {
        echo "Attention : Le chemin '$relativePath' n'existe pas et a été ignoré.\n";
    }
}

echo "\n--- 2. TRAITEMENT DES FICHIERS À LA RACINE ---\n";

$rootFilesContent = '';
$excludedExtensions = ['sql', 'md', 'lock'];

$rootIterator = new DirectoryIterator($projectRoot);

foreach ($rootIterator as $fileInfo) {
    // On ne traite que les fichiers, on ignore les dossiers et les points '.' et '..'
    if (!$fileInfo->isFile()) {
        continue;
    }

    $fileName = $fileInfo->getFilename();
    $extension = $fileInfo->getExtension();

    // Ignorer le script lui-même et les extensions exclues
    if ($fileName === 'compiler_rendu.php' || in_array($extension, $excludedExtensions)) {
        echo "Ignoré : $fileName\n";
        continue;
    }

    echo "Ajout de '$fileName' au fichier autre.txt...\n";
    $content = file_get_contents($fileInfo->getPathname());
    $rootFilesContent .= "\n/***********************************************************************************\n * Fichier: {$fileName}\n ***********************************************************************************/\n\n" . $content . "\n";
}

if (!empty(trim($rootFilesContent))) {
    $outputFile = $outputDir . DIRECTORY_SEPARATOR . 'autre.txt';
    if (file_put_contents($outputFile, $rootFilesContent)) {
        echo " -> Fichier 'autre.txt' généré avec succès.\n";
    } else {
        echo "Erreur : Impossible d'écrire dans le fichier 'autre.txt'.\n";
    }
} else {
    echo "Aucun fichier à la racine à ajouter dans 'autre.txt'.\n";
}


echo "\nOpération terminée ! Le dossier 'Rendu' a été mis à jour.\n";

?>