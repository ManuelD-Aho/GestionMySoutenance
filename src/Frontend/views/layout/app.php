<?php
// src/Frontend/views/layout/app.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$pageTitle = $pageTitle ?? 'Tableau de Bord';
$is_development = ($_ENV['APP_ENV'] ?? 'production') === 'development';
$asset_version = '1.0.1';
$cache_buster = $is_development ? time() : $asset_version;
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?> - GestionMySoutenance</title>

    <!-- CSS Principal (Tailwind + DaisyUI) -->
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $cache_buster ?>">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>

    <!-- Scripts principaux -->
    <script src="/assets/js/app.js?v=<?= $cache_buster ?>" defer></script>

    <!-- Configuration JS globale -->
    <script>
        window.AppConfig = {
            baseUrl: '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>',
            csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>',
            userRole: '<?= $_SESSION['user_data']['id_groupe_utilisateur'] ?? 'guest' ?>'
        };
    </script>
</head>
<body class="font-poppins bg-base-200 text-base-content">

<div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

    <!-- Contenu de la page -->
    <div class="drawer-content flex flex-col">
        <!-- Header -->
        <?php require_once __DIR__ . '/../_partials/_header.php'; ?>

        <!-- Contenu principal -->
        <main class="flex-1 p-4 md:p-6 lg:p-8 overflow-y-auto">
            <!-- Messages Flash -->
            <?php require_once __DIR__ . '/../_partials/_flash_messages.php'; ?>

            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <div class="text-center p-8 bg-error text-error-content rounded-box">
                    <h2 class="font-bold text-xl">Erreur de chargement</h2>
                    <p>Le contenu de cette page n'a pas pu être chargé.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Menu latéral (Sidebar) -->
    <div class="drawer-side">
        <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
        <?php require_once __DIR__ . '/../_partials/_sidebar.php'; ?>
    </div>
</div>

</body>
</html>