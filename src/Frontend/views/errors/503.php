<?php
// /src/Frontend/views/errors/503.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$errorMessage = $error_message ?? "L'application est actuellement en cours de maintenance. Veuillez réessayer plus tard.";
?>

<div class="text-center">
    <div class="mb-8">
        <span class="material-icons text-info" style="font-size: 80px;">engineering</span>
    </div>
    <h1 class="text-5xl font-extrabold text-info font-montserrat">503</h1>
    <h2 class="text-2xl font-bold mt-4">Service Indisponible</h2>
    <p class="text-base-content/70 mt-4 max-w-md mx-auto">
        <?= e($errorMessage) ?>
    </p>
    <div class="mt-8">
        <p class="text-sm text-base-content/60">Nous nous excusons pour la gêne occasionnée.</p>
    </div>
</div>