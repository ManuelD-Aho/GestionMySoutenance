<?php
// Variables attendues du contrôleur :
// $title (string) - Titre de la page
// $token (string|null) - Le token de réinitialisation
// $error_message (string|null) - Message d'erreur

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}
?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" style="max-width: 500px;">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h1 class="text-2xl font-bold text-center text-gray-700 mb-6"><?= htmlspecialchars($pageTitle ?? 'Réinitialiser le Mot de Passe', ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (isset($error_message) && $error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($token) && !empty($token)): ?>
            <form action="/reset-password" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nouveau_mot_de_passe">
                        Nouveau mot de passe
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="nouveau_mot_de_passe"
                           name="nouveau_mot_de_passe"
                           type="password"
                           placeholder="******************"
                           required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirmer_mot_de_passe">
                        Confirmer le nouveau mot de passe
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                           id="confirmer_mot_de_passe"
                           name="confirmer_mot_de_passe"
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
            <?php if (!isset($error_message)): // Afficher ce message seulement si aucun autre message d'erreur n'est déjà affiché ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Token invalide ou manquant. Veuillez refaire une demande de réinitialisation.</span>
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

