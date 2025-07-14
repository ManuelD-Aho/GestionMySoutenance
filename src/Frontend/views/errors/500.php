<?php
// /src/Frontend/views/errors/500.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$errorMessage = $error_message ?? "Une erreur inattendue est survenue de notre côté. Notre équipe technique a été notifiée.";
?>

<div class="text-center">
    <div class="mb-8">
        <span class="material-icons text-error" style="font-size: 80px;">report_problem</span>
    </div>
    <h1 class="text-5xl font-extrabold text-error font-montserrat">500</h1>
    <h2 class="text-2xl font-bold mt-4">Erreur Interne du Serveur</h2>
    <p class="text-base-content/70 mt-4 max-w-md mx-auto">
        <?= e($errorMessage) ?>
    </p>
    <div class="mt-8">
        <button onclick="window.location.reload()" class="btn btn-primary">
            <span class="material-icons mr-2">refresh</span>
            Réessayer
        </button>
    </div>
</div>