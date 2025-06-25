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

<div class="container mx-auto p-4 sm:p-6 lg:p-8" style="max-width: 500px;">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h1 class="text-2xl font-bold text-center text-gray-700 mb-6"><?= htmlspecialchars($pageTitle ?? 'Résultat de la validation d\'Email', ENT_QUOTES, 'UTF-8') ?></h1>

        <?php
        // Affichage des messages flash (passés par BaseController via $flash_messages)
        if ($has_flash_messages) {
            foreach ($flash_messages as $type => $msg) {
                if (!empty($msg)) {
                    // Utilisation de classes Tailwind pour les alertes
                    echo '<div class="bg-' . htmlspecialchars($type) . '-100 border border-' . htmlspecialchars($type) . '-400 text-' . htmlspecialchars($type) . '-700 px-4 py-3 rounded relative mb-4" role="alert">';
                    echo '<span class="block sm:inline">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</span>';
                    echo '</div>';
                }
            }
        } elseif (!empty($display_message)) {
            // Si pas de flash messages, afficher le message direct passé par le contrôleur
            // Style basé sur le statut si passé, sinon un message générique
            $alert_class = ($validation_status ?? false) ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            echo '<div class="' . $alert_class . ' px-4 py-3 rounded relative mb-4" role="alert">';
            echo '<span class="block sm:inline">' . htmlspecialchars($display_message, ENT_QUOTES, 'UTF-8') . '</span>';
            echo '</div>';
        } else {
            // Message par défaut si rien n'est spécifié
            echo '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">';
            echo '<span class="block sm:inline">Vérification de l\'email en cours ou résultat non spécifié.</span>';
            echo '</div>';
        }
        ?>

        <div class="text-center mt-4">
            <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="/login">
                Retour à la page de connexion
            </a>
        </div>
    </div>
    <p class="text-center text-gray-500 text-xs">
        &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
    </p>
</div>