<?php
// File: src/Frontend/views/layouts/app.php
// Description: Fichier de layout principal pour les pages après connexion (dashboards).
//              Il inclut le header, le menu, et la zone de contenu dynamique.

// Assurer que la session est démarrée pour accéder aux informations de l'utilisateur
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer l'utilisateur depuis la session. S'il n'est pas là, on pourrait rediriger vers /login.
// Cette vérification devrait idéalement être faite dans le contrôleur avant de rendre cette vue.
$currentUser = $_SESSION['user'] ?? null;
$userRole = $_SESSION['user_role_label'] ?? 'Invité'; // Le libellé du rôle, ex: "Administrateur Système"
// Ce $userRole devrait être défini lors de la connexion.

// $pageTitle, $menuItems, et $contentView sont passés par le contrôleur
// $pageTitle: Titre de la page actuelle.
// $menuItems: Tableau des éléments de menu spécifiques au rôle.
// $contentView: Chemin vers la vue de contenu spécifique au dashboard du rôle.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Tableau de Bord') ?> - Gestion MySoutenance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard_style.css">
    <script>
        // Configuration de Tailwind (optionnel, pour des personnalisations)
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#2563eb', // Bleu
                        'secondary': '#10B981', // Vert
                        'accent': '#8B5CF6', // Violet
                        'neutral-dark': '#1f2937', // Gris foncé pour sidebar
                        'neutral-light': '#f3f4f6', // Gris clair pour fond de contenu
                    }
                }
            }
        }
    </script>
    <style>
        /* Styles de base si dashboard_style.css n'est pas suffisant ou pour des ajustements rapides */
        body { font-family: 'Roboto', sans-serif; }
        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .sidebar-brand-text { display: none; }
        .sidebar:not(.collapsed) .sidebar-icon-toggle-closed { display: none; }
        .sidebar.collapsed .sidebar-icon-toggle-open { display: none; }
        .sidebar.collapsed .sidebar-icon { margin-left: auto; margin-right: auto; }

        @media (max-width: 1023px) { /* Tailwind 'lg' breakpoint */
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                position: fixed;
                z-index: 40; /* Inférieur au header mobile pour le bouton de toggle */
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
    // Inclure le menu. Il aura besoin de $menuItems et de l'URI actuelle.
    // Vous passerez $menuItems depuis le contrôleur, basé sur $userRole.
    // $currentUri est utilisé pour marquer l'élément de menu actif.
    $currentUri = $_SERVER['REQUEST_URI'];
    if (false !== $pos = strpos($currentUri, '?')) {
        $currentUri = substr($currentUri, 0, $pos);
    }
    include ROOT_PATH . '/src/Frontend/views/common/menu.php';
    ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php
        // Inclure le header. Il aura besoin de $pageTitle et $currentUser.
        include ROOT_PATH . '/src/Frontend/views/common/header.php';
        ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-neutral-light p-6">
            <?php if (isset($contentView) && file_exists($contentView)): ?>
                <?php include $contentView; // Inclusion du contenu spécifique au dashboard du rôle ?>
            <?php elseif (isset($contentHtml)): ?>
                <?= $contentHtml // Alternative si le contrôleur génère directement du HTML ?>
            <?php else: ?>
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <h1 class="text-2xl font-semibold text-gray-700">Bienvenue</h1>
                    <p class="text-gray-600 mt-2">Contenu du tableau de bord non spécifié ou introuvable.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const desktopCollapseButton = document.getElementById('desktopCollapseButton');

    function toggleSidebarDesktop() {
        sidebar.classList.toggle('collapsed');
        // Optionnel : Sauvegarder la préférence dans localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    function toggleSidebarMobile() {
        sidebar.classList.toggle('open');
    }

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', toggleSidebarMobile);
    }
    if (desktopCollapseButton) {
        desktopCollapseButton.addEventListener('click', toggleSidebarDesktop);
    }

    // Restaurer l'état de la sidebar au chargement pour desktop
    if (window.innerWidth >= 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

    // Fermer la sidebar mobile si on clique en dehors (optionnel)
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 1024 && sidebar.classList.contains('open')) {
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


