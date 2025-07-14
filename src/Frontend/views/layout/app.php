<?php
// /src/Frontend/views/layout/app.php

// Fonction d'échappement pour la sécurité
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Variables par défaut pour éviter les erreurs
$pageTitle = $pageTitle ?? 'GestionMySoutenance';
$user = $user ?? null;
$menu_items = $menu_items ?? [];
$flash_messages = $flash_messages ?? [];
$content = $content ?? '<p class="text-error">Erreur: Contenu de la page non défini.</p>';

// Détermine la version des assets pour le cache-busting
$is_development = ($_ENV['APP_ENV'] ?? 'production') === 'development';
$asset_version = '1.0.2'; // Incrémenter cette version lors de changements majeurs
$cache_buster = $is_development ? time() : $asset_version;
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?> - GestionMySoutenance</title>

    <!-- CSS Principal (compilé depuis src/css/input.css) -->
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $cache_buster ?>">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Configuration JS globale pour l'application -->
    <script>
        window.AppConfig = {
            baseUrl: '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>',
            csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>', // Note: CSRF token should be managed per form for better security
            userRole: '<?= e($user['id_groupe_utilisateur'] ?? 'guest') ?>'
        };
    </script>
</head>
<body class="font-poppins bg-base-200 text-base-content">

<div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

    <!-- Contenu de la page (droite) -->
    <div class="drawer-content flex flex-col">

        <!-- Header -->
        <?php require_once __DIR__ . '/header.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-4 md:p-6 lg:p-8 overflow-y-auto">

            <!-- Messages Flash -->
            <?php require_once __DIR__ . '/_flash_messages.php'; ?>

            <!-- Injection du contenu de la vue spécifique -->
            <?= $content ?>

        </main>
    </div>

    <!-- Menu latéral (Sidebar - gauche) -->
    <div class="drawer-side">
        <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>

        <!-- Inclusion du menu -->
        <?php require_once __DIR__ . '/menu.php'; ?>
    </div>
</div>

<!-- Scripts JS (compilé depuis src/js/app.js) -->
<script src="/assets/js/app.js?v=<?= $cache_buster ?>" defer></script>
</body>
</html>