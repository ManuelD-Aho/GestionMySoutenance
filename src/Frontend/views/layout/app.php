<?php
// src/Frontend/views/layout/app.php

// Fonction d'échappement HTML, au cas où elle ne serait pas déjà définie globalement
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// $pageTitle doit être défini par le contrôleur de la vue spécifique chargée
// Si non défini, on fournit un titre par défaut
$pageTitle = $pageTitle ?? 'GestionMySoutenance - Tableau de Bord';

// Démarrer la session si ce n'est pas déjà fait pour que $_SESSION soit disponible
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Les messages flash doivent être récupérés et effacés APRES leur affichage
// Ici, on les passe comme une variable au header ou au main content
$flash_messages = $_SESSION['flash_messages'] ?? [];
unset($_SESSION['flash_messages']); // Effacer après récupération

// Passer les messages flash à la vue pour qu'elle puisse les afficher (ou un composant global)
$data['flash_messages'] = $flash_messages;

// Récupérer l'URL courante pour que le menu puisse marquer l'élément actif
$current_url = $_SERVER['REQUEST_URI'];
$current_url = strtok($current_url, '?'); // Nettoyer les paramètres GET
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?></title>

    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/style.css"> <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="sidebar-open"> <?php
// Inclusion du header
// Passe $pageTitle et d'autres données nécessaires au header
// Assurez-vous que les variables $currentUser, $notificationCount, etc. sont disponibles
// soit par $_SESSION directement, soit passées via $data au header.php
$header_data = [
    'pageTitle' => $pageTitle,
    // D'autres variables comme $currentUser, $notificationCount etc. seront lues directement par header.php via $_SESSION
];
require_once __DIR__ . '/common/header.php';
?>

<?php
// Inclusion du menu latéral
// Passe les données nécessaires au menu pour la construction dynamique
$menu_data = [
    'current_url' => $current_url,
    // Les permissions et user_data sont lues directement par menu.php via $_SESSION
];
require_once __DIR__ . '/common/menu.php';
?>

<main class="main-content-area" id="mainContentArea">
    <?php
    // Cette variable $content doit être définie par le routeur/contrôleur
    // qui inclut ce layout et charge la vue spécifique.
    if (isset($content)) {
        require_once $content; // $content devrait être le chemin absolu vers la vue (ex: __DIR__ . '/Administration/dashboard_admin.php')
    } else {
        // Fallback si aucun contenu n'est fourni (peut être utile pour le débogage)
        echo '<div class="admin-module-container admin-card text-center mt-xl">';
        echo '<h2>Contenu de la page non chargé.</h2>';
        echo '<p class="text-muted">Veuillez vérifier le routeur ou le contrôleur qui utilise ce layout.</p>';
        echo '</div>';
    }
    ?>
</main>

<script src="/assets/js/main.js"></script> <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de la page titre dans le header via la fonction globale
        if (typeof DashboardHeader !== 'undefined' && typeof DashboardHeader.updatePageTitle === 'function') {
            DashboardHeader.updatePageTitle(<?= json_encode($pageTitle); ?>);
        }

        // Gestion de l'affichage des messages flash globaux (si pas déjà affichés par une vue spécifique)
        const globalFlashMessages = <?= json_encode($flash_messages); ?>;
        if (Object.keys(globalFlashMessages).length > 0) {
            for (const type in globalFlashMessages) {
                if (globalFlashMessages.hasOwnProperty(type) && globalFlashMessages[type]) {
                    // Supposons une fonction showNotification globale dans main.js qui peut afficher ces messages
                    if (typeof DashboardHeader !== 'undefined' && typeof DashboardHeader.showNotification === 'function') {
                        DashboardHeader.showNotification(globalFlashMessages[type], type);
                    } else {
                        console.warn('DashboardHeader.showNotification non disponible pour les messages flash.');
                    }
                }
            }
        }
    });
</script>
</body>
</html>