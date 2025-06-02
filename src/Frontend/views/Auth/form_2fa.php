<?php
// Variables attendues du contrôleur :
// $title (string) - Titre de la page
// $error (string|null) - Message d'erreur à afficher

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}
?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" style="max-width: 400px;">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h1 class="text-2xl font-bold text-center text-gray-700 mb-6"><?= htmlspecialchars($pageTitle ?? 'Vérification à Deux Facteurs', ENT_QUOTES, 'UTF-8') ?></h1>

        <p class="text-gray-600 text-sm mb-4 text-center">
            Veuillez entrer le code généré par votre application d'authentification.
        </p>

        <?php if (isset($error) && $error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="/login-2fa" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="code_2fa">
                    Code d'Authentification
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="code_2fa"
                       name="code_2fa"
                       type="text"
                       placeholder="123456"
                       required
                       pattern="\d{6}"
                       title="Le code doit être composé de 6 chiffres."
                       autocomplete="one-time-code">
            </div>

            <div class="flex items-center justify-center">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Vérifier
                </button>
            </div>
        </form>
    </div>
    <p class="text-center text-gray-500 text-xs">
        &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
    </p>
</div>

