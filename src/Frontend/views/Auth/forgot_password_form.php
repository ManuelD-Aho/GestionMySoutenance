<?php
// Variables attendues du contrôleur :
// $title (string) - Titre de la page
// $success_message (string|null) - Message de succès
// $error_message (string|null) - Message d'erreur
// $form_data (array) - Données du formulaire précédemment soumises

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}

$email_value = isset($form_data['email_principal']) ? htmlspecialchars($form_data['email_principal'], ENT_QUOTES, 'UTF-8') : '';
?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" style="max-width: 500px;">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h1 class="text-2xl font-bold text-center text-gray-700 mb-6"><?= htmlspecialchars($pageTitle ?? 'Mot de Passe Oublié', ENT_QUOTES, 'UTF-8') ?></h1>

        <p class="text-gray-600 text-sm mb-4 text-center">
            Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>

        <?php if (isset($success_message) && $success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message) && $error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="/forgot-password" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email_principal">
                    Adresse E-mail
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="email_principal"
                       name="email_principal"
                       type="email"
                       placeholder="votreadresse@example.com"
                       value="<?= $email_value ?>"
                       required>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Envoyer le lien de réinitialisation
                </button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="/login">
                    Retour à la connexion
                </a>
            </div>
        </form>
    </div>
    <p class="text-center text-gray-500 text-xs">
        &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
    </p>
</div>

