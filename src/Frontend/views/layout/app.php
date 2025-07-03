<?php
// src/Frontend/views/layout/app.php

// Les variables essentielles passées par BaseController::render()
// $title, $content (maintenant le chemin du fichier de vue), $flash_messages, $user, $is_impersonating, $impersonator_data, $menu_items
// sont disponibles dans ce scope grâce à extract($data) dans BaseController.

$title = $title ?? 'GestionMySoutenance'; // Titre par défaut

// Récupérer l'URL courante pour que le menu puisse marquer l'élément actif
$current_url = $_SERVER['REQUEST_URI'];
$current_url = strtok($current_url, '?'); // Nettoyer les paramètres GET
?>
<!DOCTYPE html>
<html lang="fr" data-theme="mytheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="/assets/favicon.ico">

    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/root.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <script src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js" defer></script>
    <script src="https://unpkg.com/gsap@3.12.5/dist/ScrollTrigger.min.js" defer></script>

    <script src="/assets/js/app.js" defer></script>
    <script src="/assets/js/main.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-poppins antialiased bg-base-200 text-base-content">

<div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col min-h-screen">
        <?php
        // Inclusion du header
        // Les variables comme $user, $is_impersonating, etc. sont disponibles via le scope global ici.
        require_once __DIR__ . '/common/header.php';
        ?>

        <div class="p-6 pt-4">
            <?php if (!empty($flash_messages)): ?>
                <div class="space-y-3">
                    <?php foreach ($flash_messages as $msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($msg['type']) ?> shadow-lg rounded-lg animate-fade-in-up">
                            <div>
                                <i class="fas fa-<?= $msg['type'] === 'success' ? 'check-circle' : ($msg['type'] === 'error' ? 'times-circle' : ($msg['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?> text-xl"></i>
                                <span class="font-medium"><?= htmlspecialchars($msg['message']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <main class="flex-grow p-6 bg-base-200">
            <?php
            // Cette variable $content est maintenant le chemin absolu vers la vue spécifique (ex: dashboard_admin.php)
            if (isset($content) && file_exists($content)) {
                require_once $content; // Inclut le contenu de la vue spécifique ici
            } else {
                echo '<div class="admin-module-container admin-card text-center mt-xl">';
                echo '<h2>Contenu de la page non chargé.</h2>';
                echo '<p class="text-muted">Le fichier de vue spécifié n\'existe pas ou $content n\'est pas défini.</p>';
                echo '</div>';
            }
            ?>
        </main>

        <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-200 shadow-inner">
            <aside>
                <p class="text-sm">Copyright © <?= date('Y') ?> - Tous droits réservés par <span class="font-semibold text-primary">GestionMySoutenance</span></p>
            </aside>
        </footer>
    </div>

    <?php
    // Inclusion du menu latéral
    // Les variables comme $menu_items, $current_url sont disponibles via le scope global ici.
    require_once __DIR__ . '/common/menu.php';
    ?>

    <div id="notifications-panel" class="fixed inset-y-0 right-0 w-80 bg-base-100 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out p-6 border-l border-base-200">
        <div class="flex justify-between items-center mb-6 border-b pb-4 border-base-200">
            <h3 class="text-2xl font-bold text-primary font-montserrat">Notifications</h3>
            <button class="btn btn-sm btn-ghost text-base-content/70 hover:text-primary transition-colors duration-200" id="close-notifications" aria-label="Fermer le panneau de notifications">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="space-y-4 overflow-y-auto h-[calc(100vh-120px)] pr-2">
            <div class="alert alert-info shadow-md rounded-lg animate-fade-in-right">
                <div>
                    <i class="fas fa-info-circle text-xl"></i>
                    <span class="font-medium">Bienvenue sur votre tableau de bord !</span>
                </div>
                <div class="text-xs text-base-content/60 mt-1">Il y a 5 minutes</div>
            </div>
            <div class="alert alert-warning shadow-md rounded-lg animate-fade-in-right">
                <div>
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                    <span class="font-medium">Votre email n'est pas validé. Veuillez vérifier votre boîte de réception.</span>
                </div>
                <div class="text-xs text-base-content/60 mt-1">Il y a 2 heures</div>
            </div>
            <div class="alert alert-success shadow-md rounded-lg animate-fade-in-right">
                <div>
                    <i class="fas fa-check-circle text-xl"></i>
                    <span class="font-medium">Votre rapport a été validé avec succès !</span>
                </div>
                <div class="text-xs text-base-content/60 mt-1">Hier</div>
            </div>
            <div class="alert alert-error shadow-md rounded-lg animate-fade-in-right">
                <div>
                    <i class="fas fa-times-circle text-xl"></i>
                    <span class="font-medium">Erreur lors de la soumission de votre rapport.</span>
                </div>
                <div class="text-xs text-base-content/60 mt-1">Il y a 3 jours</div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full p-4 bg-base-100 border-t border-base-200">
            <button class="btn btn-sm btn-block btn-outline btn-primary">Voir toutes les notifications</button>
        </div>
    </div>
</div>
</body>
</html>