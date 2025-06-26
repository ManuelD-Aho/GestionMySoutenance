<?php
// src/Frontend/views/Auth/reset_password_form.php - Version corrigée
// Variables attendues du contrôleur :
// $title (string) - Titre de la page
// $token (string|null) - Le token de réinitialisation
// $flash_messages (array) - Messages flash (success, error, warning, info)
// $csrf_token (string) - Jeton CSRF généré par le BaseController

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}
?>

<style>
    /* Reset CSS */
    .reset-password-container * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', 'Roboto', sans-serif;
    }

    .reset-password-container {
        background: linear-gradient(135deg, #f0f7ff, #e1eeff);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .reset-password-wrapper {
        width: 100%;
        max-width: 500px;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .reset-password-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(50, 120, 220, 0.15);
        overflow: hidden;
        padding: 40px;
        position: relative;
        z-index: 1;
    }

    .reset-password-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        z-index: 2;
    }

    .reset-password-header {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        padding-top: 20px;
    }

    .reset-password-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 10px;
        letter-spacing: -0.5px;
    }

    .reset-password-alert {
        padding: 16px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        font-size: 15px;
        display: flex;
        align-items: center;
        animation: slideIn 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .reset-password-alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 6px;
    }

    .alert-success {
        background-color: rgba(46, 204, 113, 0.1);
        border-left: 4px solid #2ecc71;
        color: #166534;
    }

    .alert-error {
        background-color: rgba(231, 76, 60, 0.1);
        border-left: 4px solid #e74c3c;
        color: #c0392b;
    }

    .alert-warning {
        background-color: rgba(243, 156, 18, 0.1);
        border-left: 4px solid #f39c12;
        color: #b45309;
    }

    .alert-info {
        background-color: rgba(52, 152, 219, 0.1);
        border-left: 4px solid #3498db;
        color: #1d4ed8;
    }

    .reset-password-form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .reset-password-label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 15px;
        color: #1e293b;
    }

    .reset-password-input-wrapper {
        position: relative;
    }

    .reset-password-input {
        width: 100%;
        padding: 16px 20px;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.25s ease;
        background-color: #f8fafc;
        color: #1e293b;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .reset-password-input:focus {
        outline: none;
        border-color: #93c5fd;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.25);
    }

    .password-toggle {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #94a3b8;
        font-size: 20px;
        user-select: none;
        transition: color 0.2s ease;
    }

    .password-toggle:hover {
        color: #3b82f6;
    }

    .reset-password-button {
        background: linear-gradient(to right, #3b82f6, #2563eb);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 17px;
        font-size: 17px;
        font-weight: 600;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.35);
        letter-spacing: 0.5px;
    }

    .reset-password-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
    }

    .reset-password-button:active {
        transform: translateY(0);
    }

    .reset-password-link {
        display: block;
        text-align: center;
        color: #3b82f6;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.25s ease;
        margin-top: 25px;
        padding: 10px;
        border-radius: 8px;
    }

    .reset-password-link:hover {
        color: #1e40af;
        background-color: rgba(59, 130, 246, 0.08);
        text-decoration: underline;
    }

    .reset-password-footer {
        text-align: center;
        margin-top: 30px;
        color: #64748b;
        font-size: 14px;
        padding: 0 20px;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .reset-password-card {
            padding: 30px 25px;
        }

        .reset-password-header h1 {
            font-size: 24px;
        }

        .reset-password-input {
            padding: 14px 18px;
        }

        .reset-password-button {
            padding: 15px;
            font-size: 16px;
        }
    }
</style>

<div class="reset-password-container">
    <div class="reset-password-wrapper">
        <div class="reset-password-card">
            <div class="reset-password-header">
                <h1><?= htmlspecialchars($pageTitle ?? 'Réinitialiser le Mot de Passe', ENT_QUOTES, 'UTF-8') ?></h1>
            </div>

            <?php
            // Affichage des messages flash (passés par BaseController via $flash_messages)
            if (isset($flash_messages) && is_array($flash_messages)) {
                foreach ($flash_messages as $type => $message) {
                    // S'assurer que le message n'est pas vide avant de l'afficher
                    if ($message) {
                        echo '<div class="reset-password-alert alert-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '" role="alert">';
                        echo '<span class="block sm:inline">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</span>';
                        echo '</div>';
                    }
                }
            }
            ?>

            <?php if (isset($token) && !empty($token)): ?>
                <form action="/reset-password" method="POST">
                    <!-- CHAMP CSRF CACHÉ - AJOUT ESSENTIEL POUR LA SÉCURITÉ -->
                    <!-- La variable $csrf_token est passée automatiquement par le BaseController::render() -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="reset-password-form-group">
                        <label class="reset-password-label" for="new_password">
                            Nouveau mot de passe
                        </label>
                        <div class="reset-password-input-wrapper">
                            <input class="reset-password-input"
                                   id="new_password"
                                   name="new_password"
                                   type="password"
                                   placeholder="Entrez votre nouveau mot de passe"
                                   required>
                            <span class="password-toggle" onclick="togglePasswordVisibility('new_password')">👁️</span>
                        </div>
                    </div>

                    <div class="reset-password-form-group">
                        <label class="reset-password-label" for="confirm_password">
                            Confirmer le nouveau mot de passe
                        </label>
                        <div class="reset-password-input-wrapper">
                            <input class="reset-password-input"
                                   id="confirm_password"
                                   name="confirm_password"
                                   type="password"
                                   placeholder="Confirmez votre nouveau mot de passe"
                                   required>
                            <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">👁️</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center">
                        <button class="reset-password-button" type="submit">
                            Réinitialiser le mot de passe
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <?php // Ce bloc s'exécute si le token est manquant/vide, et n'est plus conditionné par !isset($error_message) ?>
                <?php if (empty($flash_messages['error'])): // Vérifie si aucun message d'erreur spécifique n'a été défini par le contrôleur ?>
                    <div class="reset-password-alert alert-warning" role="alert">
                        <span class="block sm:inline">Le lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.</span>
                    </div>
                <?php endif; ?>
                <div class="text-center">
                    <a class="reset-password-link" href="/forgot-password">
                        Demander un nouveau lien
                    </a>
                </div>
            <?php endif; ?>

            <div class="reset-password-footer">
                <p class="text-center text-gray-500 text-xs">
                    &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour basculer la visibilité du mot de passe
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const toggle = field.nextElementSibling;

        if (field.type === "password") {
            field.type = "text";
            toggle.textContent = "🔒";
        } else {
            field.type = "password";
            toggle.textContent = "👁️";
        }
    }

    // Validation du formulaire
    document.querySelector('form')?.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            alert('Les mots de passe ne correspondent pas.');
            e.preventDefault();
            return;
        }

        if (newPassword.length < 8) {
            alert('Le mot de passe doit contenir au moins 8 caractères.');
            e.preventDefault();
            return;
        }
    });
</script>