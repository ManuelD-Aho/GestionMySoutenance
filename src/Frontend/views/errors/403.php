<?php
// /src/Frontend/views/errors/403.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$errorMessage = $error_message ?? "Vous n'avez pas les permissions nécessaires pour accéder à cette page.";
?>

<div class="text-center">
    <div class="mb-8">
        <span class="material-icons text-error" style="font-size: 80px;">lock</span>
    </div>
    <h1 class="text-5xl font-extrabold text-error font-montserrat">403</h1>
    <h2 class="text-2xl font-bold mt-4">Accès Interdit</h2>
    <p class="text-base-content/70 mt-4 max-w-md mx-auto">
        <?= e($errorMessage) ?>
    </p>
    <div class="mt-8">
        <a href="/dashboard" class="btn btn-primary">
            <span class="material-icons mr-2">home</span>
            Retour au Tableau de Bord
        </a>
    </div>
</div>