<?php

/**
 * Script pour agréger le contenu de dossiers spécifiques et des fichiers à la racine
 * d'un projet dans des fichiers .txt uniques pour le rendu.
 *
 * USAGE : php generer_rendu.php
 */

// --- CONFIGURATION ---

// Chemin racine du projet où se trouve ce script.
$projectRoot = __DIR__;

// Dossier de destination pour les fichiers .txt générés.
$outputDir = $projectRoot . '/Rendu';

// Liste des dossiers à traiter. Chaque dossier sera compilé dans son propre fichier .txt.
// Par exemple, 'Public' deviendra 'Rendu/Public.txt'.
$pathsToProcess = [
    'Public',
    'routes',
    'docker',
    'src/Config',
    'src/css',
    'src/Frontend',
    'src/Backend/Controller',
    'src/Backend/Exception',
    'src/Backend/Model',
    'src/Backend/Service',
    'src/Backend/Util',
];

// Dossiers à ignorer complètement lors du parcours des chemins ci-dessus.
// Note : Le dossier 'assets' n'est PAS ignoré pour qu'il soit inclus dans Public.txt.
$ignoredFolders = [
    'vendor',
    'node_modules',
    '.git',
    '.idea',
    '.vscode',
    basename($outputDir) // Le dossier de rendu lui-même.
];

// Fichiers à la racine à exclure du fichier 'autre.txt'.
$excludedRootFiles = [
    'generer_rendu.php', // Le script lui-même.
    'mysoutenance.sql',
    'composer.lock',
    'package-lock.json',
    'README.md',
    'Fonction.md',
    'Commande.txt',
    'code.php', // Ancien script de génération
    'seeds.php',
];


// --- FIN DE LA CONFIGURATION ---


/**
 * Fonction récursive pour agréger le contenu des fichiers d'un dossier.
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

    // Utilisation d'un itérateur récursif pour parcourir tous les fichiers et dossiers.
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $pathname = $item->getPathname();

        // Vérifie si le chemin contient un des dossiers à ignorer.
        foreach ($ignoredFolders as $ignored) {
            if (strpos($pathname, DIRECTORY_SEPARATOR . $ignored . DIRECTORY_SEPARATOR) !== false) {
                continue 2; // Passe à l'élément suivant de la boucle externe.
            }
        }

        if ($item->isFile() && $item->isReadable()) {
            $fileContent = file_get_contents($pathname);

            if ($fileContent === false) {
                echo "Attention : Impossible de lire le contenu du fichier '$pathname'.\n";
                continue;
            }

            // Formatte l'en-tête pour chaque fichier.
            $relativePath = str_replace($rootPath . DIRECTORY_SEPARATOR, '', $pathname);
            $content .= "\n/***********************************************************************************\n * Fichier: {$relativePath}\n ***********************************************************************************/\n\n" . $fileContent . "\n";
        }
    }
    return $content;
}

// Crée le dossier de destination s'il n'existe pas.
if (!is_dir($outputDir)) {
    if (!mkdir($outputDir, 0755, true)) {
        die("Erreur critique : Impossible de créer le répertoire de destination '$outputDir'.\n");
    }
    echo "Répertoire '$outputDir' créé avec succès.\n";
}

echo "\n--- Début de l'agrégation du projet ---\n\n";

// --- 1. TRAITEMENT DES DOSSIERS SPÉCIFIQUES ---
echo "1. Traitement des dossiers de l'application...\n";
foreach ($pathsToProcess as $relativePath) {
    $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $relativePath;

    if (is_dir($fullPath)) {
        echo "   - Traitement de '$relativePath'...\n";
        $aggregatedContent = getAggregatedContent($fullPath, $projectRoot, $ignoredFolders);

        if (empty(trim($aggregatedContent))) {
            echo "     -> Aucun contenu trouvé, fichier non généré.\n";
            continue;
        }

        $outputFileName = str_replace(['/', '\\'], '_', $relativePath) . '.txt';
        $outputFile = $outputDir . DIRECTORY_SEPARATOR . $outputFileName;

        if (file_put_contents($outputFile, $aggregatedContent) === false) {
            echo "     -> ERREUR : Impossible d'écrire dans le fichier '$outputFile'.\n";
        } else {
            echo "     -> Fichier '$outputFileName' généré avec succès.\n";
        }
    } else {
        echo "   - ATTENTION : Le chemin '$relativePath' n'existe pas et a été ignoré.\n";
    }
}

// --- 2. TRAITEMENT DES FICHIERS À LA RACINE ---
echo "\n2. Traitement des fichiers de configuration à la racine...\n";
$rootFilesContent = '';
$rootIterator = new DirectoryIterator($projectRoot);

foreach ($rootIterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    $fileName = $fileInfo->getFilename();

    if (in_array($fileName, $excludedRootFiles)) {
        continue;
    }

    echo "   - Ajout de '$fileName' au fichier autre.txt...\n";
    $content = file_get_contents($fileInfo->getPathname());
    $rootFilesContent .= "\n/***********************************************************************************\n * Fichier: {$fileName}\n ***********************************************************************************/\n\n" . $content . "\n";
}

if (!empty(trim($rootFilesContent))) {
    $outputFile = $outputDir . DIRECTORY_SEPARATOR . 'autre.txt';
    if (file_put_contents($outputFile, $rootFilesContent)) {
        echo "   -> Fichier 'autre.txt' généré avec succès.\n";
    } else {
        echo "   -> ERREUR : Impossible d'écrire dans le fichier 'autre.txt'.\n";
    }
} else {
    echo "   -> Aucun fichier à la racine à traiter.\n";
}

echo "\n--- Opération terminée ! Le dossier 'Rendu' a été mis à jour. ---\n";

?>
