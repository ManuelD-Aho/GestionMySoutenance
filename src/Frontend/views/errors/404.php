<?php
// /src/Frontend/views/errors/404.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$errorMessage = $error_message ?? "La page que vous recherchez n'existe pas ou a été déplacée.";
?>

<div class="text-center">
    <div class="mb-8">
        <span class="material-icons text-warning" style="font-size: 80px;">explore_off</span>
    </div>
    <h1 class="text-5xl font-extrabold text-warning font-montserrat">404</h1>
    <h2 class="text-2xl font-bold mt-4">Page Non Trouvée</h2>
    <p class="text-base-content/70 mt-4 max-w-md mx-auto">
        <?= e($errorMessage) ?>
    </p>
    <div class="mt-8">
        <a href="/" class="btn btn-primary">
            <span class="material-icons mr-2">home</span>
            Retour à l'accueil
        </a>
    </div>
</div>