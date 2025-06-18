<?php
// Variables attendues du contrôleur :
// $title (string) - Titre de la page
// $error (string|null) - Message d'erreur à afficher
// $success_message (string|null) - Message de succès à afficher
// $login_data (array) - Données du formulaire précédemment soumises (pour pré-remplissage)

// Assurer la compatibilité avec le layout app.php qui attend $pageTitle
if (!isset($pageTitle) && isset($title)) {
    $pageTitle = $title;
}

// Valeurs par défaut pour les données du formulaire
$identifiant_value = isset($login_data['identifiant']) ? htmlspecialchars($login_data['identifiant'], ENT_QUOTES, 'UTF-8') : '';

?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8" style="max-width: 500px;">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h1 class="text-2xl font-bold text-center text-gray-700 mb-6"><?= htmlspecialchars($pageTitle ?? 'Connexion', ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (isset($error) && $error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message) && $success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">

            <?= $this->getCsrfInput() ?>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="identifiant">
                    Identifiant (Email ou Numéro)
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="identifiant"
                       name="identifiant"
                       type="text"
                       placeholder="Votre identifiant"
                       value="<?= $identifiant_value ?>"
                       required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="mot_de_passe">
                    Mot de passe
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                       id="mot_de_passe"
                       name="mot_de_passe"
                       type="password"
                       placeholder="******************"
                       required>
                <!-- Vous pourriez ajouter ici un message d'erreur spécifique au champ mot de passe si nécessaire -->
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Se connecter
                </button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="/forgot-password">
                    Mot de passe oublié?
                </a>
            </div>

            <!-- Optionnel: Lien d'inscription si applicable -->
            <!--
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    Pas encore de compte? <a href="/register" class="font-bold text-blue-500 hover:text-blue-800">S'inscrire</a>
                </p>
            </div>
            -->
        </form>
    </div>
    <p class="text-center text-gray-500 text-xs">
        &copy;<?= date('Y') ?> GestionMySoutenance. Tous droits réservés.
    </p>
</div>
