<?php
// src/Frontend/views/Auth/form_2fa_setup.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données (QR code URL, secret key) proviendraient du contrôleur AuthentificationController
//
//

$qr_code_url = $data['qr_code_url'] ?? 'https://via.placeholder.com/200?text=QR+Code'; // URL du QR code généré
$secret_key = $data['secret_key'] ?? 'YOURSECRETKEY12345'; // Clé secrète manuelle
$error_message = $_SESSION['2fa_error'] ?? null;
$success_message = $_SESSION['2fa_success'] ?? null;

// Nettoyer les messages après les avoir récupérés
unset($_SESSION['2fa_error']);
unset($_SESSION['2fa_success']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration 2FA - GestionMySoutenance</title>
    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Configuration de l'Authentification à Deux Facteurs (2FA)</h1>
            <p class="auth-subtitle">Sécurisez votre compte en ajoutant une couche de protection supplémentaire.</p>
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

        <div class="setup-2fa-instructions">
            <p><strong>Étape 1 : Scannez le code QR</strong></p>
            <p>Ouvrez votre application d'authentification (Google Authenticator, Authy, etc.) et scannez le code QR ci-dessous.</p>
            <div class="qr-code-container">
                <img src="<?= e($qr_code_url); ?>" alt="Code QR pour 2FA">
            </div>
            <p class="text-center mt-md">Ou entrez la clé manuellement : <code class="secret-key"><?= e($secret_key); ?></code></p>

            <p class="mt-xl"><strong>Étape 2 : Vérifiez la configuration</strong></p>
            <p>Une fois le compte ajouté à votre application, saisissez le code à 6 chiffres généré par celle-ci pour vérifier la configuration.</p>

            <form id="verify2faForm" action="/setup-2fa/verify" method="POST">
                <div class="form-group">
                    <label for="verification_code">Code de vérification :</label>
                    <input type="text" id="verification_code" name="verification_code" maxlength="6" pattern="\d{6}" required placeholder="Ex: 123456">
                    <small class="form-help">Saisissez le code à 6 chiffres de votre application d'authentification.</small>
                </div>

                <button type="submit" class="btn btn-primary-green btn-full-width">
                    <span class="material-icons">verified_user</span> Vérifier et Activer 2FA
                </button>
            </form>
        </div>

        <div class="auth-links">
            <a href="/profile" class="link-secondary">Retour au profil</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('verify2faForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                const verificationCode = document.getElementById('verification_code').value.trim();

                if (!verificationCode) {
                    alert('Veuillez saisir le code de vérification.');
                    event.preventDefault();
                    return;
                }

                if (!/^\d{6}$/.test(verificationCode)) {
                    alert('Le code de vérification doit être composé de 6 chiffres.');
                    event.preventDefault();
                    return;
                }

                console.log("Formulaire de vérification 2FA soumis.");
            });
        }
    });
</script>

<style>
    /* Styles spécifiques pour form_2fa_setup.php */
    /* Réutilisation des classes de root.css et auth.css */

    /* .auth-container et .auth-card sont déjà stylisés dans auth.css */
    /* .auth-header, .auth-title, .auth-subtitle sont déjà stylisés dans auth.css */
    /* .alert, .alert-error, .alert-success sont déjà stylisés dans auth.css */

    .setup-2fa-instructions {
        text-align: center;
        margin-top: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .setup-2fa-instructions p {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
    }

    .setup-2fa-instructions p strong {
        color: var(--primary-blue-dark);
        font-size: var(--font-size-lg);
    }

    .qr-code-container {
        background-color: var(--primary-white);
        border: 1px solid var(--border-medium);
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: inline-block; /* Pour centrer le bloc */
        margin: var(--spacing-md) auto;
        box-shadow: var(--shadow-sm);
    }

    .qr-code-container img {
        display: block;
        width: 200px; /* Taille fixe pour le QR code */
        height: 200px;
        object-fit: contain;
    }

    .secret-key {
        background-color: var(--primary-gray-light);
        color: var(--text-primary);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-sm);
        font-family: monospace;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        user-select: all; /* Permet de facilement copier la clé */
    }

    /* Formulaire de vérification (réutilisation des styles de formulaire d'auth.css) */
    #verify2faForm {
        margin-top: var(--spacing-lg);
        text-align: left; /* Aligne les labels à gauche */
    }

    #verify2faForm .form-group {
        margin-bottom: var(--spacing-lg);
    }

    #verify2faForm label {
        display: block;
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        font-size: var(--font-size-sm);
    }

    #verify2faForm input[type="text"] {
        width: 100%;
        padding: var(--spacing-md);
        border: var(--border-width-thin) solid var(--border-light);
        border-radius: var(--border-radius-lg);
        font-size: var(--font-size-base);
        transition: all var(--transition-fast);
        background-color: var(--bg-primary);
        color: var(--text-primary);
        text-align: center; /* Centre le code de vérification */
        letter-spacing: 2px; /* Espacement entre les chiffres */
    }

    #verify2faForm input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        transform: translateY(-1px);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
        text-align: center;
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
        text-align: center; /* Centre les liens de retour */
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

    /* Footer de la page (réutilisé de auth.css) */
    .email-validation-footer { /* Renommé du style global de email-validation pour être plus générique aux pages auth standalone */
        text-align: center;
        margin-top: var(--spacing-xl); /* Plus d'espace au-dessus du footer */
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
        padding: 0 20px;
    }

</style>