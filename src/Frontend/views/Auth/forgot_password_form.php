<?php
// src/Frontend/views/Auth/forgot_password_form.php - Version FINALEMENT corrigée
// Variables attendues du contrôleur :
// $title (string) - Titre de la page
// $flash_messages (array) - Messages flash (success, error, warning, info)
// $form_data (array) - Données du formulaire précédemment soumises
// $csrf_token (string) - Jeton CSRF généré par le BaseController

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}

$email_value = isset($form_data['email_principal']) ? htmlspecialchars($form_data['email_principal'], ENT_QUOTES, 'UTF-8') : '';
?>

<style>
    /* Style pour la page de mot de passe oublié */
    .forgot-password-container {
        background: linear-gradient(135deg, #e6f2ff, #c2d9ff);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .forgot-password-wrapper {
        width: 100%;
        max-width: 500px;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .forgot-password-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(50, 120, 220, 0.15);
        overflow: hidden;
        padding: 40px;
        position: relative;
        z-index: 1;
    }

    .forgot-password-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        z-index: 2;
    }

    .forgot-password-header {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        padding-top: 20px;
    }

    .forgot-password-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 10px;
        letter-spacing: -0.5px;
    }

    .forgot-password-description {
        color: #64748b;
        font-size: 16px;
        text-align: center;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    /* Messages flash */
    .forgot-password-alert {
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

    .forgot-password-alert::before {
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

    /* Formulaire */
    .forgot-password-form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .forgot-password-label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 15px;
        color: #1e293b;
    }

    .forgot-password-input-wrapper {
        position: relative;
    }

    .forgot-password-input {
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

    .forgot-password-input:focus {
        outline: none;
        border-color: #93c5fd;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.25);
    }

    .forgot-password-button {
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

    .forgot-password-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
    }

    .forgot-password-button:active {
        transform: translateY(0);
    }

    .forgot-password-link {
        display: block;
        text-align: center;
        color: #3b82f6;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.25s ease;
        margin-top: 20px;
        padding: 10px 15px;
        border-radius: 8px;
    }

    .forgot-password-link:hover {
        color: #1e40af;
        background-color: rgba(59, 130, 246, 0.1);
        text-decoration: none;
    }

    .forgot-password-footer {
        text-align: center;
        margin-top: 30px;
        color: #64748b;
        font-size: 14px;
        padding: 0 20px;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .forgot-password-card {
            padding: 30px 25px;
        }

        .forgot-password-header h1 {
            font-size: 24px;
        }

        .forgot-password-input {
            padding: 14px 18px;
        }

        .forgot-password-button {
            padding: 15px;
            font-size: 16px;
        }

        .forgot-password-actions {
            flex-direction: column;
            gap: 15px;
        }

        .forgot-password-button, .forgot-password-link {
            width: 100%;
        }
    }
</style>

<div class="forgot-password-container">
    <div class="forgot-password-wrapper">
        <div class="forgot-password-card">
            <div class="forgot-password-header">
                <h1><?= htmlspecialchars($pageTitle ?? 'Mot de Passe Oublié', ENT_QUOTES, 'UTF-8') ?></h1>
            </div>

            <p class="forgot-password-description">
                Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </p>

            <?php
            // Affichage des messages flash
            if (isset($flash_messages) && is_array($flash_messages)) {
                foreach ($flash_messages as $type => $msg) {
                    if (!empty($msg)) {
                        echo '<div class="forgot-password-alert alert-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '" role="alert">';
                        echo '<span class="block sm:inline">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</span>';
                        echo '</div>';
                    }
                }
            }
            ?>

            <form action="/forgot-password" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <div class="forgot-password-form-group">
                    <label class="forgot-password-label" for="email_principal">
                        Adresse E-mail
                    </label>
                    <div class="forgot-password-input-wrapper">
                        <input class="forgot-password-input"
                               id="email_principal"
                               name="email_principal"
                               type="email"
                               placeholder="votreadresse@example.com"
                               value="<?= $email_value ?>"
                               required>
                    </div>
                </div>

                <div class="forgot-password-actions">
                    <button type="submit" class="forgot-password-button">
                        Envoyer le lien de réinitialisation
                    </button>
                    <a href="/login" class="forgot-password-link">
                        Retour à la connexion
                    </a>
                </div>
            </form>

            <div class="forgot-password-footer">
                <p class="text-center text-gray-500 text-xs">
                    &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</div>