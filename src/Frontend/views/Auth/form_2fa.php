<?php
// src/Frontend/views/Auth/form_2fa.php - Version corrigée
// Variables attendues du contrôleur :
// $title (string) - Titre de la page
// $flash_messages (array) - Messages flash (success, error, warning, info)
// $csrf_token (string) - Jeton CSRF généré par le BaseController

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}
?>

<style>
    /* Style pour la page 2FA */
    .twofa-container {
        background: linear-gradient(135deg, #e6f2ff, #c2d9ff);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .twofa-wrapper {
        width: 100%;
        max-width: 450px;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .twofa-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(50, 120, 220, 0.15);
        overflow: hidden;
        padding: 40px;
        position: relative;
        z-index: 1;
    }

    .twofa-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        z-index: 2;
    }

    .twofa-header {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        padding-top: 20px;
    }

    .twofa-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 10px;
        letter-spacing: -0.5px;
    }

    .twofa-icon {
        font-size: 60px;
        margin-bottom: 25px;
        display: block;
        text-align: center;
        color: #3b82f6;
    }

    .twofa-description {
        color: #64748b;
        font-size: 16px;
        text-align: center;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    /* Messages flash */
    .twofa-alert {
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

    .twofa-alert::before {
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
    .twofa-form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .twofa-label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 15px;
        color: #1e293b;
        text-align: center;
    }

    .twofa-input-wrapper {
        position: relative;
        max-width: 280px;
        margin: 0 auto;
    }

    .twofa-input {
        width: 100%;
        padding: 16px 20px;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        font-size: 20px;
        transition: all 0.25s ease;
        background-color: #f8fafc;
        color: #1e293b;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        text-align: center;
        letter-spacing: 8px;
    }

    .twofa-input:focus {
        outline: none;
        border-color: #93c5fd;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.25);
    }

    .twofa-button {
        background: linear-gradient(to right, #3b82f6, #2563eb);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 17px;
        font-size: 17px;
        font-weight: 600;
        width: 100%;
        max-width: 280px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin: 20px auto 0;
        display: block;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.35);
        letter-spacing: 0.5px;
    }

    .twofa-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
    }

    .twofa-button:active {
        transform: translateY(0);
    }

    .twofa-footer {
        text-align: center;
        margin-top: 30px;
        color: #64748b;
        font-size: 14px;
        padding: 0 20px;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .twofa-card {
            padding: 30px 25px;
        }

        .twofa-header h1 {
            font-size: 24px;
        }

        .twofa-input {
            padding: 14px 18px;
            font-size: 18px;
            letter-spacing: 6px;
        }

        .twofa-button {
            padding: 15px;
            font-size: 16px;
        }
    }
</style>

<div class="twofa-container">
    <div class="twofa-wrapper">
        <div class="twofa-card">
            <div class="twofa-header">
                <span class="twofa-icon">🔒</span>
                <h1><?= htmlspecialchars($pageTitle ?? 'Vérification à Deux Facteurs', ENT_QUOTES, 'UTF-8') ?></h1>
            </div>

            <p class="twofa-description">
                Veuillez entrer le code à 6 chiffres généré par votre application d'authentification.
            </p>

            <?php
            // Affichage des messages flash (passés par BaseController via $flash_messages)
            if (isset($flash_messages) && is_array($flash_messages)) {
                foreach ($flash_messages as $type => $message) {
                    if ($message) {
                        echo '<div class="twofa-alert alert-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '" role="alert">';
                        echo '<span class="block sm:inline">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</span>';
                        echo '</div>';
                    }
                }
            }
            ?>

            <!-- L'action du formulaire doit pointer vers la route POST /2fa -->
            <form action="/2fa" method="POST">
                <!-- CHAMP CSRF CACHÉ - AJOUT ESSENTIEL POUR LA SÉCURITÉ -->
                <!-- La variable $csrf_token est passée automatiquement par le BaseController::render() -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <div class="twofa-form-group">
                    <label class="twofa-label" for="code_2fa">
                        Code d'Authentification
                    </label>
                    <div class="twofa-input-wrapper">
                        <input class="twofa-input"
                               id="code_2fa"
                               name="code_2fa"
                               type="text"
                               placeholder="••••••"
                               required
                               pattern="\d{6}"
                               title="Le code doit être composé de 6 chiffres."
                               autocomplete="one-time-code"
                               maxlength="6">
                    </div>
                </div>

                <button class="twofa-button" type="submit">
                    Vérifier
                </button>
            </form>

            <div class="twofa-footer">
                <p class="text-center text-gray-500 text-xs">
                    &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</div>