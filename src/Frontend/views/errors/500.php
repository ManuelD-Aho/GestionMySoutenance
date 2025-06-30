<?php
// src/Frontend/views/errors/500.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Variables pour le contenu (peuvent être passées par un contrôleur d'erreurs)
$pageTitle = $data['pageTitle'] ?? 'Erreur Serveur Interne (500)';
$errorMessage = $data['errorMessage'] ?? 'Une erreur inattendue est survenue sur le serveur.';
$homeLink = $data['homeLink'] ?? '/dashboard'; // Lien vers le tableau de bord ou la page d'accueil
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - GestionMySoutenance</title>
    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/style.css"> <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<div class="error-container">
    <div class="error-card">
        <div class="error-header">
            <span class="material-icons error-icon">cloud_off</span>
            <h1>500</h1>
        </div>
        <h2 class="error-title"><?= e($pageTitle); ?></h2>
        <p class="error-message"><?= e($errorMessage); ?></p>
        <p class="error-suggestion">Nous sommes désolés pour ce désagrément. Veuillez réessayer ultérieurement ou contacter l'administrateur du système.</p>
        <a href="<?= e($homeLink); ?>" class="btn btn-primary-blue error-link">
            <span class="material-icons">arrow_back</span> Retour au Tableau de Bord
        </a>
    </div>
</div>

</body>
</html>