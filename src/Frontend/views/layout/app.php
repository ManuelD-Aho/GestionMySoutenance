<?php
// File: src/Frontend/views/layout/app.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ces variables sont utilisées par le header et le menu, elles doivent être définies
// par les contrôleurs qui utilisent ce layout. Pour la page de login, elles seront probablement nulles.
$currentUser = $_SESSION['user_complet'] ?? null; // Utiliser user_complet comme défini dans ServiceAuthentification
$userRole = null;
if ($currentUser && isset($currentUser->libelle_type_utilisateur)) {
    $userRole = $currentUser->libelle_type_utilisateur;
} elseif(isset($_SESSION['id_type_utilisateur'])) { // Fallback si user_complet n'est pas entièrement peuplé
    // Vous auriez besoin d'une logique pour mapper id_type_utilisateur à un libellé si nécessaire ici
    // Pour l'instant, on peut le laisser vide ou mettre une valeur par défaut.
    // $userRole = 'Rôle ID: ' . $_SESSION['id_type_utilisateur'];
}
$userRole = $userRole ?? 'Invité';


// $pageTitle est passé par le contrôleur.
// $menuItems devrait être passé par le contrôleur pour les pages connectées.
// Pour la page de login, $menuItems ne sera pas pertinent ou sera vide.
// $contentForLayout est la variable clé qui contient le HTML de la vue spécifique (ex: Auth/login.php)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Gestion MySoutenance', ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard_style.css"> <?php // Assurez-vous que ce chemin est correct ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#2563eb',
                        'secondary': '#10B981',
                        'accent': '#8B5CF6',
                        'neutral-dark': '#1f2937',
                        'neutral-light': '#f3f4f6',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Roboto', sans-serif; }
        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .sidebar-brand-text { display: none; }
        .sidebar:not(.collapsed) .sidebar-icon-toggle-closed { display: none; }
        .sidebar.collapsed .sidebar-icon-toggle-open { display: none; }
        .sidebar.collapsed .sidebar-icon { margin-left: auto; margin-right: auto; }

        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                position: fixed;
                z-index: 40;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .header-mobile-menu-button { display: block; }
        }
        @media (min-width: 1024px) {
            .header-mobile-menu-button { display: none; }
        }
    </style>
</head>
<body class="bg-neutral-light">
<div class="flex h-screen">
    <?php
    // Pour la page de login, on ne voudra probablement pas du menu principal.
    // On pourrait ajouter une condition ici pour ne l'afficher que si l'utilisateur est connecté.
    // Par exemple: if (isset($currentUser)) { include ROOT_PATH . '/src/Frontend/views/common/menu.php'; }
    // Pour l'instant, on le laisse pour voir si le reste fonctionne.
    // Assurez-vous que $menuItems est défini (même un tableau vide) pour éviter les erreurs dans menu.php
    $menuItems = $menuItems ?? [];
    $currentUri = $_SERVER['REQUEST_URI'];
    if (false !== $pos = strpos($currentUri, '?')) {
        $currentUri = substr($currentUri, 0, $pos);
    }
    // Condition pour afficher le menu uniquement si l'utilisateur est connecté
    if (isset($_SESSION['numero_utilisateur'])) { // ou if($currentUser)
        include ROOT_PATH . '/src/Frontend/views/common/menu.php';
    }
    ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php
        // Idem pour le header, conditionner son affichage.
        // if (isset($currentUser)) { include ROOT_PATH . '/src/Frontend/views/common/header.php'; }
        if (isset($_SESSION['numero_utilisateur'])) { // ou if($currentUser)
            include ROOT_PATH . '/src/Frontend/views/common/header.php';
        }
        ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto <?= isset($_SESSION['numero_utilisateur']) ? 'bg-neutral-light p-6' : 'bg-gray-100' ?>">
            <?php
            // *** CORRECTION PRINCIPALE ICI ***
            // Afficher le contenu préparé par BaseController::render()
            if (isset($contentForLayout)) {
                echo $contentForLayout;
            } elseif (isset($contentView) && file_exists($contentView)) { // Garder comme fallback si vous l'utilisez ailleurs
                include $contentView;
            } elseif (isset($contentHtml)) { // Garder comme fallback
                echo $contentHtml;
            } else {
                // Ce bloc ne devrait plus être atteint pour les vues normales si $contentForLayout est toujours fourni.
                echo '<div class="bg-white p-8 rounded-lg shadow-md">';
                echo '<h1 class="text-2xl font-semibold text-gray-700">Bienvenue</h1>';
                echo '<p class="text-gray-600 mt-2">Aucun contenu spécifique à afficher.</p>';
                echo '</div>';
            }
            ?>
        </main>
    </div>
</div>

<script>
    // Votre JavaScript existant pour la sidebar
    const sidebar = document.getElementById('sidebar');
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const desktopCollapseButton = document.getElementById('desktopCollapseButton');

    function toggleSidebarDesktop() {
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
    }

    function toggleSidebarMobile() {
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    }

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', toggleSidebarMobile);
    }
    if (desktopCollapseButton) {
        desktopCollapseButton.addEventListener('click', toggleSidebarDesktop);
    }

    if (sidebar && window.innerWidth >= 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

    document.addEventListener('click', function(event) {
        if (sidebar && mobileMenuButton && window.innerWidth < 1024 && sidebar.classList.contains('open')) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnMobileButton = mobileMenuButton.contains(event.target);
            if (!isClickInsideSidebar && !isClickOnMobileButton) {
                sidebar.classList.remove('open');
            }
        }
    });
</script>
</body>
</html>
