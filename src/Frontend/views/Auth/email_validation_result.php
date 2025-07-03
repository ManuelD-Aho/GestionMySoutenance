<?php
// src/Frontend/views/Auth/email_validation_result.php

// Fonction d'échappement HTML pour sécuriser l'affichage
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Variables attendues du contrôleur :
// $pageTitle (string) - Titre de la page (ex: 'Validation Email Réussie', 'Validation Email Échouée')
// $flash_messages (array) - Tableau associatif des messages flash (ex: ['success' => 'Votre email a été validé.'])
// $validation_status (bool) - Vrai si la validation a réussi, Faux sinon.
// $message (string) - Message spécifique à afficher (si pas de flash messages).

// Assurer les valeurs par défaut
$pageTitle = $data['pageTitle'] ?? 'Résultat de la validation d\'Email';
$flash_messages = $data['flash_messages'] ?? [];
$validation_status = $data['validation_status'] ?? false; // Par défaut, échoué
$display_message = $data['message'] ?? '';

// Déterminer si des messages flash existent pour les afficher en priorité
$has_flash_messages = !empty(array_filter($flash_messages));

// Si aucun message direct ni flash message, fournir un message par défaut
if (!$has_flash_messages && empty($display_message)) {
    $display_message = $validation_status ? 'Votre adresse email a été validée avec succès.' : 'La validation de votre email a échoué. Veuillez réessayer ou contacter le support.';
    // Définir le type d'alerte par défaut si le message n'est pas flashé
    $default_alert_type = $validation_status ? 'success' : 'error';
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - GestionMySoutenance</title>
    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<div class="email-validation-container">
    <div class="email-validation-wrapper">
        <div class="email-validation-card">
            <div class="email-validation-header">
                <h1><?= e($pageTitle) ?></h1>
            </div>

            <?php
            // Afficher une icône en fonction du statut de validation
            if ($validation_status) {
                echo '<span class="email-validation-icon icon-success"><span class="material-icons">check_circle</span></span>';
            } else {
                echo '<span class="email-validation-icon icon-error"><span class="material-icons">cancel</span></span>';
            }
            ?>

            <?php
            // Affichage des messages (priorité aux flash messages)
            if ($has_flash_messages) {
                foreach ($flash_messages as $type => $msg_content) {
                    if (!empty($msg_content)) {
                        echo '<div class="email-validation-alert alert-' . e($type) . '" role="alert">';
                        echo e($msg_content);
                        echo '</div>';
                    }
                }
            } elseif (!empty($display_message)) {
                // Si pas de flash messages, afficher le message direct
                echo '<div class="email-validation-alert alert-' . e($default_alert_type ?? 'info') . '" role="alert">';
                echo e($display_message);
                echo '</div>';
            }
            ?>

            <a class="email-validation-link" href="/login">
                Retour à la page de connexion
            </a>

            <div class="email-validation-footer">
                <p>&copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Aucune logique JavaScript complexe n'est généralement nécessaire pour cette page statique de résultat.
        // Les messages sont affichés directement via PHP.
    });
</script>
</body>
</html>