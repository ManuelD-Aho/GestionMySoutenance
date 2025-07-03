<?php
// src/Frontend/views/Auth/change_password_form.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Récupérer les messages d'erreur ou de succès potentiels de la session
$error_message = $_SESSION['change_password_error'] ?? null;
$success_message = $_SESSION['change_password_success'] ?? null;
$requires_old_password = $_SESSION['requires_old_password'] ?? true; // Dépend du contexte (changement vs réinitialisation)

// Nettoyer les messages après les avoir récupérés
unset($_SESSION['change_password_error']);
unset($_SESSION['change_password_success']);
unset($_SESSION['requires_old_password']);

// Si la page est accessible via un token de réinitialisation, l'ancien mot de passe n'est pas requis
$is_reset_flow = isset($_GET['token']); // Indique que c'est une réinitialisation post-demande
if ($is_reset_flow) {
    $requires_old_password = false;
}

?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Changer votre mot de passe</h1>
            <p class="auth-subtitle">
                <?php if ($is_reset_flow): ?>
                    Veuillez définir un nouveau mot de passe pour votre compte.
                <?php else: ?>
                    Mettez à jour votre mot de passe actuel.
                <?php endif; ?>
            </p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error" role="alert">
                <span class="material-icons">error_outline</span>
                <?= e($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <span class="material-icons">check_circle_outline</span>
                <?= e($success_message); ?>
            </div>
        <?php endif; ?>

        <form id="changePasswordForm" action="/change-password<?= $is_reset_flow ? '?token=' . e($_GET['token']) : ''; ?>" method="POST">
            <?php if ($requires_old_password): ?>
                <div class="form-group">
                    <label for="old_password">Ancien mot de passe :</label>
                    <input type="password" id="old_password" name="old_password" required autocomplete="current-password">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                <small class="form-help">Minimum 8 caractères, incluant majuscule, minuscule, chiffre et caractère spécial.</small>
            </div>
            <div class="form-group">
                <label for="confirm_new_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary-green btn-full-width">
                <span class="material-icons">vpn_key</span> Changer le Mot de Passe
            </button>
        </form>

        <div class="auth-links">
            <a href="/login" class="link-secondary">Retour à la connexion</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('changePasswordForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                const newPassword = document.getElementById('new_password').value;
                const confirmNewPassword = document.getElementById('confirm_new_password').value;
                const oldPassword = document.getElementById('old_password') ? document.getElementById('old_password').value : null;

                // Validation de base des champs vides
                if (!newPassword || !confirmNewPassword || (<?= json_encode($requires_old_password); ?> && !oldPassword)) {
                    alert("Veuillez remplir tous les champs obligatoires.");
                    event.preventDefault();
                    return;
                }

                // Validation de la correspondance des nouveaux mots de passe
                if (newPassword !== confirmNewPassword) {
                    alert("Le nouveau mot de passe et sa confirmation ne correspondent pas.");
                    event.preventDefault();
                    return;
                }

                // Validation de la complexité du mot de passe (vous pouvez la rendre plus stricte)
                // Minimum 8 caractères, au moins une majuscule, une minuscule, un chiffre et un caractère spécial
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}\[\]:;"'<>,.?\/\\-]).{8,}$/;
                if (!passwordRegex.test(newPassword)) {
                    alert("Le nouveau mot de passe doit contenir au moins 8 caractères, incluant une majuscule, une minuscule, un chiffre et un caractère spécial.");
                    event.preventDefault();
                    return;
                }

                console.log("Formulaire de changement de mot de passe soumis.");
            });
        }

        // Le script auth.js est censé gérer d'autres aspects généraux de l'authentification.
        // Assurez-vous qu'il est inclus et exécuté si besoin.
    });
</script>

<style>
    /* Styles spécifiques pour change_password_form.php */
    /* Réutilisation des classes de root.css et auth.css */

    /* Les styles généraux de .auth-container, .auth-card, .auth-header, .auth-title, .auth-subtitle
       sont déjà définis dans auth.css ou injectés via le layout auth.php */

    .form-group {
        margin-bottom: var(--spacing-md);
        text-align: left;
    }

    .form-group label {
        display: block;
        font-size: var(--font-size-sm);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group input[type="password"] {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
    }

    .form-group input:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    /* Boutons */
    .btn {
        padding: var(--spacing-md) var(--spacing-lg);
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        border: none;
        border-radius: var(--border-radius-md);
        cursor: pointer;
        transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
        text-decoration: none;
    }

    .btn-primary-green {
        background-color: var(--primary-green);
        color: var(--text-white);
    }

    .btn-primary-green:hover {
        background-color: var(--primary-green-dark);
        box-shadow: var(--shadow-md);
    }

    .btn-full-width {
        width: 100%;
        margin-top: var(--spacing-md);
    }

    .auth-links {
        margin-top: var(--spacing-lg);
        font-size: var(--font-size-sm);
    }

    .link-secondary {
        color: var(--primary-blue);
        text-decoration: none;
        transition: color var(--transition-fast);
    }

    .link-secondary:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }

    /* Alertes (réutilisation) */
    .alert {
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-md);
        text-align: left;
    }

    .alert-error {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
        border: 1px solid var(--accent-red-dark);
    }

    .alert-success {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
        border: 1px solid var(--primary-green-dark);
    }

    .alert .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }
</style>