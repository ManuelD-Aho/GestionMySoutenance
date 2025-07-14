<?php
// /src/Frontend/views/layout/layout_auth.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'GestionMySoutenance';
$content = $content ?? '<p class="text-error">Erreur: Contenu du formulaire non défini.</p>';
$asset_version = '1.0.2';
$cache_buster = ($_ENV['APP_ENV'] ?? 'production') === 'development' ? time() : $asset_version;
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?> - GestionMySoutenance</title>

    <!-- CSS Principal -->
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $cache_buster ?>">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="antialiased">

<div class="flex min-h-screen">
    <!-- Colonne de gauche avec le carrousel d'images (cachée sur mobile) -->
    <div class="hidden lg:block w-1/2 xl:w-3/5 bg-primary relative">
        <div class="carousel-container w-full h-full">
            <img src="/assets/images/auth/soutenance1.jpg" alt="Étudiants présentant leur soutenance" class="carousel-image active">
            <img src="/assets/images/auth/soutenance2.jpg" alt="Jury de soutenance en délibération" class="carousel-image">
            <img src="/assets/images/auth/soutenance3.jpg" alt="Campus universitaire moderne" class="carousel-image">
        </div>
        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-end p-12 text-white">
            <div id="carousel-text-content">
                <h1 class="font-montserrat text-4xl xl:text-5xl font-extrabold mb-4">Gérez vos soutenances avec excellence.</h1>
                <p class="text-lg xl:text-xl opacity-80">Une plateforme centralisée pour simplifier chaque étape du processus académique.</p>
            </div>
        </div>
        <div class="carousel-indicators absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-3">
            <button class="indicator w-3 h-3 rounded-full bg-white/40 transition-all duration-300 active" data-slide="0"></button>
            <button class="indicator w-3 h-3 rounded-full bg-white/40 transition-all duration-300" data-slide="1"></button>
            <button class="indicator w-3 h-3 rounded-full bg-white/40 transition-all duration-300" data-slide="2"></button>
        </div>
    </div>

    <!-- Colonne de droite avec le contenu du formulaire -->
    <div class="w-full lg:w-1/2 xl:w-2/5 flex items-center justify-center p-6 sm:p-12 bg-base-100">
        <div class="w-full max-w-md">
            <!-- Injection du contenu du formulaire (login, reset, etc.) -->
            <?= $content ?>
        </div>
    </div>
</div>

<!-- Scripts JS -->
<script src="/assets/js/app.js?v=<?= $cache_buster ?>" defer></script>
</body>
</html>
