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

<div class="container mx-auto p-4 sm:p-6 lg:p-8" style="max-width: 500px;">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h1 class="text-2xl font-bold text-center text-gray-700 mb-6"><?= htmlspecialchars($pageTitle ?? 'Réinitialiser le Mot de Passe', ENT_QUOTES, 'UTF-8') ?></h1>

        <?php
        // Affichage des messages flash (passés par BaseController via $flash_messages)
        if (isset($flash_messages) && is_array($flash_messages)) {
            foreach ($flash_messages as $type => $message) {
                // S'assurer que le message n'est pas vide avant de l'afficher
                if ($message) {
                    // Utilisation de classes Tailwind pour les alertes (assurez-vous qu'elles sont dans votre CSS global ou un CDN)
                    // Note: Le style 'bg-red-100 border border-red-400 text-red-700' est un exemple,
                    // assurez-vous que 'bg-' . htmlspecialchars($type) . '-100' fonctionne avec vos classes CSS.
                    // Si ce n'est pas le cas, utilisez des classes fixes comme 'alert-success', 'alert-error'
                    echo '<div class="bg-' . htmlspecialchars($type) . '-100 border border-' . htmlspecialchars($type) . '-400 text-' . htmlspecialchars($type) . '-700 px-4 py-3 rounded relative mb-4" role="alert">';
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

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="new_password">
                        Nouveau mot de passe
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="new_password" <!-- Renommé pour cohérence avec le contrôleur -->
                    name="new_password" <!-- Renommé pour cohérence avec le contrôleur -->
                    type="password"
                    placeholder="******************"
                    required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                        Confirmer le nouveau mot de passe
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                           id="confirm_password" <!-- Renommé pour cohérence avec le contrôleur -->
                    name="confirm_password" <!-- Renommé pour cohérence avec le contrôleur -->
                    type="password"
                    placeholder="******************"
                    required>
                </div>

                <div class="flex items-center justify-center">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Réinitialiser le mot de passe
                    </button>
                </div>
            </form>
        <?php else: ?>
            <?php // Ce bloc s'exécute si le token est manquant/vide, et n'est plus conditionné par !isset($error_message) ?>
            <?php if (empty($flash_messages['error'])): // Vérifie si aucun message d'erreur spécifique n'a été défini par le contrôleur ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Le lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.</span>
                </div>
            <?php endif; ?>
            <div class="text-center">
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="/forgot-password">
                    Demander un nouveau lien
                </a>
            </div>
        <?php endif; ?>
    </div>
    <p class="text-center text-gray-500 text-xs">
        &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
    </p>
</div>