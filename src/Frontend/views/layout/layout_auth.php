<?php
// src/Frontend/views/layout/layout_auth.php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'GestionMySoutenance';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?></title>
    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="auth-layout">
<div class="auth-container">
    <?php if (isset($content)): ?>
        <?= $content; ?>
    <?php else: ?>
        <div class="error-message">
            <h2>Erreur de chargement</h2>
            <p>Le contenu de la page n'a pas pu être chargé.</p>
        </div>
    <?php endif; ?>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>