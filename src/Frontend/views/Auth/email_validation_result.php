<?php
// src/Frontend/views/Auth/email_validation_result.php - Version corrigée
// Variables attendues du contrôleur :
// $title (string) - Titre de la page (ex: 'Validation Email Réussie', 'Validation Email Échouée')
// $flash_messages (array) - Messages flash (success, error, warning, info)
// $validation_status (bool) - Vrai si la validation a réussi, Faux sinon (peut être passé par le contrôleur)
// $message (string) - Message spécifique à afficher (alternative aux flash messages si plus direct)

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}

// Déterminer un message par défaut si aucun message flash n'est présent
$display_message = $message ?? '';

// Check if there are any flash messages to display, and prioritize them
$has_flash_messages = false;
if (isset($flash_messages) && is_array($flash_messages)) {
    foreach ($flash_messages as $msg) {
        if (!empty($msg)) {
            $has_flash_messages = true;
            break;
        }
    }
}
?>

<style>
    /* Reset CSS */
    .email-validation-container * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', 'Roboto', sans-serif;
    }

    .email-validation-container {
        background: linear-gradient(135deg, #f0f7ff, #e1eeff);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .email-validation-wrapper {
        width: 100%;
        max-width: 500px;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .email-validation-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(50, 120, 220, 0.15);
        overflow: hidden;
        padding: 40px;
        position: relative;
        z-index: 1;
    }

    .email-validation-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        z-index: 2;
    }

    .email-validation-header {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        padding-top: 20px;
    }

    .email-validation-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 10px;
        letter-spacing: -0.5px;
    }

    .email-validation-icon {
        font-size: 80px;
        margin-bottom: 25px;
        display: block;
        text-align: center;
    }

    .icon-success {
        color: #10b981;
    }

    .icon-error {
        color: #ef4444;
    }

    .email-validation-alert {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        font-size: 17px;
        text-align: center;
        line-height: 1.6;
        animation: slideIn 0.4s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .email-validation-alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 6px;
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.08);
        border-left: 4px solid #10b981;
        color: #047857;
    }

    .alert-error {
        background-color: rgba(239, 68, 68, 0.08);
        border-left: 4px solid #ef4444;
        color: #b91c1c;
    }

    .alert-warning {
        background-color: rgba(245, 158, 11, 0.08);
        border-left: 4px solid #f59e0b;
        color: #b45309;
    }

    .alert-info {
        background-color: rgba(59, 130, 246, 0.08);
        border-left: 4px solid #3b82f6;
        color: #1d4ed8;
    }

    .email-validation-link {
        display: block;
        text-align: center;
        color: #3b82f6;
        font-weight: 600;
        font-size: 16px;
        text-decoration: none;
        transition: all 0.25s ease;
        margin-top: 25px;
        padding: 12px 20px;
        border-radius: 10px;
        background: rgba(59, 130, 246, 0.1);
        max-width: 280px;
        margin: 30px auto 0;
    }

    .email-validation-link:hover {
        color: #1e40af;
        background-color: rgba(59, 130, 246, 0.2);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    }

    .email-validation-footer {
        text-align: center;
        margin-top: 40px;
        color: #64748b;
        font-size: 14px;
        padding: 0 20px;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .email-validation-card {
            padding: 30px 25px;
        }

        .email-validation-header h1 {
            font-size: 24px;
        }

        .email-validation-alert {
            padding: 16px;
            font-size: 16px;
        }

        .email-validation-icon {
            font-size: 60px;
        }
    }
</style>

<div class="email-validation-container">
    <div class="email-validation-wrapper">
        <div class="email-validation-card">
            <div class="email-validation-header">
                <h1><?= htmlspecialchars($pageTitle ?? 'Résultat de la validation d\'Email', ENT_QUOTES, 'UTF-8') ?></h1>
            </div>

            <?php
            // Afficher une icône en fonction du statut
            if ($validation_status ?? false) {
                echo '<span class="email-validation-icon icon-success">✓</span>';
            } else {
                echo '<span class="email-validation-icon icon-error">✕</span>';
            }
            ?>

            <?php
            // Affichage des messages flash (passés par BaseController via $flash_messages)
            if ($has_flash_messages) {
                foreach ($flash_messages as $type => $msg) {
                    if (!empty($msg)) {
                        echo '<div class="email-validation-alert alert-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '" role="alert">';
                        echo '<span class="block sm:inline">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</span>';
                        echo '</div>';
                    }
                }
            } elseif (!empty($display_message)) {
                // Si pas de flash messages, afficher le message direct passé par le contrôleur
                // Style basé sur le statut si passé, sinon un message générique
                $alert_class = ($validation_status ?? false) ? 'alert-success' : 'alert-error';
                echo '<div class="email-validation-alert ' . $alert_class . '" role="alert">';
                echo '<span class="block sm:inline">' . htmlspecialchars($display_message, ENT_QUOTES, 'UTF-8') . '</span>';
                echo '</div>';
            } else {
                // Message par défaut si rien n'est spécifié
                echo '<div class="email-validation-alert alert-info" role="alert">';
                echo '<span class="block sm:inline">Vérification de l\'email en cours ou résultat non spécifié.</span>';
                echo '</div>';
            }
            ?>

            <a class="email-validation-link" href="/login">
                Retour à la page de connexion
            </a>

            <div class="email-validation-footer">
                <p class="text-center text-gray-500 text-xs">
                    &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</div>