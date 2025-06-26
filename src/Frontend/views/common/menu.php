<?php
// src/Frontend/views/common/menu.php

use App\Config\Container;
use App\Backend\Service\Permissions\ServicePermissions;

$menuItems = []; // Tableau qui contiendra la structure hiérarchique finale du menu


if (isset($_SESSION['user_id'])) {
    $loggedInUserId = $_SESSION['user_id'];
} else {
    $loggedInUserId = null;
}

if ($loggedInUserId) {
    try {
        $container = new Container();
        $permissionsService = $container->get(ServicePermissions::class);

        $userPermissions = $permissionsService->getPermissionsForUser($loggedInUserId);





        // ======================================================================
        // NOUVELLE LOGIQUE DE CONSTRUCTION DE L'ARBRE DU MENU
        // ======================================================================
        $indexedPermissions = [];
        foreach ($userPermissions as $permission) {
            $permission['children'] = []; // Initialiser le tableau des enfants
            $indexedPermissions[$permission['id_traitement']] = $permission;
        }

        $tempMenuItems = []; // Tableau temporaire pour construire l'arbre
        foreach ($indexedPermissions as $id => $permission) {
            // Cloner l'élément pour éviter les problèmes de référence directe
            // et s'assurer que les enfants sont des copies distinctes.
            $currentItem = $permission;

            if (!empty($currentItem['id_parent_traitement']) && isset($indexedPermissions[$currentItem['id_parent_traitement']])) {
                // Si l'élément a un parent et que le parent est dans la liste des permissions,
                // l'ajouter comme enfant directement au parent dans $indexedPermissions.
                // NOTE: Puisque $indexedPermissions est traversé SANS références pour $permission,
                // nous devons recréer la référence ou s'assurer que la copie est attachée.
                // Une meilleure approche est d'attacher aux références dans indexedPermissions.

                // On utilise les références ici pour s'assurer que les enfants modifient la version dans indexedPermissions
                $indexedPermissions[$currentItem['id_parent_traitement']]['children'][] = &$indexedPermissions[$id];
            } else {
                // Si c'est un élément racine (pas de parent ou parent non récupéré)
                $tempMenuItems[] = &$indexedPermissions[$id];
            }
        }
        unset($permission); // Rompre la référence de la dernière itération

        // La variable $menuItems finale est maintenant prête
        $menuItems = $tempMenuItems;
        // ======================================================================
        // FIN NOUVELLE LOGIQUE DE CONSTRUCTION DE L'ARBRE DU MENU
        // ======================================================================

        // ... (DEBUG MENU ITEMS (BEFORE FILTER/SORT) et les autres debugs restent inchangés) ...
//        echo '<div style="background-color: #bbdefb; padding: 10px; border: 1px solid #2196f3; margin-bottom: 15px;">';
//        echo '<strong>DEBUG MENU ITEMS (BEFORE FILTER/SORT):</strong><br>';
//        if (!empty($menuItems)) {
//            echo '<pre>' . htmlspecialchars(print_r($menuItems, true)) . '</pre>';
//        } else {
//            echo 'Le tableau $menuItems est vide avant le filtre et le tri.';
//        }
//        echo '</div>';

        $menuItems = array_filter($menuItems, function($item) {
            return !empty($item['url_associee']) || !empty($item['children']);
        });

//        echo '<div style="background-color: #ffccbc; padding: 10px; border: 1px solid #ff5722; margin-bottom: 15px;">';
//        echo '<strong>DEBUG MENU ITEMS (AFTER FILTER):</strong><br>';
//        if (!empty($menuItems)) {
//            echo '<pre>' . htmlspecialchars(print_r($menuItems, true)) . '</pre>';
//        } else {
//            echo 'Le tableau $menuItems est vide après le filtre.';
//        }
//        echo '</div>';

        // Tri des éléments de menu principaux et de leurs enfants par libellé
        usort($menuItems, function($a, $b) {
            return strcmp($a['libelle_traitement'], $b['libelle_traitement']);
        });

        foreach ($menuItems as &$menuItem) {
            if (!empty($menuItem['children'])) {
                usort($menuItem['children'], function($a, $b) {
                    return strcmp($a['libelle_traitement'], $b['libelle_traitement']);
                });
            }
        }
        unset($menuItem);

    } catch (\Exception $e) {
        error_log("Erreur critique lors de la construction du menu pour l'utilisateur {$loggedInUserId}: " . $e->getMessage());
        echo '<div style="background-color: #ffcdd2; padding: 10px; border: 1px solid #f44336; margin-bottom: 15px;">';
        echo '<strong>ERREUR CRITIQUE MENU:</strong> ' . htmlspecialchars($e->getMessage()) . '<br>';
        echo 'Vérifiez les logs PHP pour plus de détails.';
        echo '</div>';
        $menuItems = [];
    }
}

// ... (renderMenuItems, styles et scripts restent inchangés) ...
function renderMenuItems(array $items, bool $isSubMenu = false): void
{
    $listClass = $isSubMenu ? 'submenu' : 'main-menu';
    echo '<ul class="' . $listClass . '">';

    foreach ($items as $item) {
        if (empty($item['libelle_traitement'])) {
            continue;
        }

        $hasChildren = !empty($item['children']);

        $linkTarget = '#';
        if (!empty($item['url_associee'])) {
            $linkTarget = '/' . htmlspecialchars($item['url_associee']);
        } elseif ($hasChildren) {
            $linkTarget = '#';
        }

        $itemClass = $hasChildren ? 'has-submenu' : '';

        echo '<li class="' . $itemClass . '">';
        echo '<a href="' . $linkTarget . '">';

        if (!empty($item['icone_class'])) {
            echo '<i class="' . htmlspecialchars($item['icone_class']) . ' menu-icon"></i> ';
        }

        echo htmlspecialchars($item['libelle_traitement']);

        if ($hasChildren) {
            echo ' <i class="fas fa-caret-down submenu-caret"></i>';
        }

        echo '</a>';

        if ($hasChildren) {
            renderMenuItems($item['children'], true);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<nav class="sidebar-nav">
    <?php
    if (!empty($menuItems)) {
        renderMenuItems($menuItems);
    } else {
        echo '<p class="no-menu-available">Aucun menu disponible pour cet utilisateur ou session non connectée.</p>';
    }
    ?>
</nav>

<style>
    /* Styles CSS, inchangés */
    .sidebar-nav {
        background-color: #2c3e50;
        color: #ecf0f1;
        padding: 15px 0;
        font-family: 'Inter', sans-serif;
        border-radius: 8px;
        overflow-y: auto;
        height: 100%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .main-menu, .submenu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .main-menu > li, .submenu > li {
        position: relative;
        margin-bottom: 5px;
    }

    .main-menu > li:last-child, .submenu > li:last-child {
        margin-bottom: 0;
    }

    .main-menu > li > a, .submenu > li > a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #ecf0f1;
        text-decoration: none;
        transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
        border-radius: 8px;
        margin: 0 10px;
        background-color: #314457;
    }

    .main-menu > li > a:hover, .submenu > li > a:hover {
        background-color: #3a5068;
        color: #ffffff;
        box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.3);
        transform: translateY(-2px);
    }

    .submenu {
        background-color: #34495e;
        margin-top: 5px;
        padding-left: 20px;
        border-radius: 8px;
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
        display: none;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding-top 0.3s ease-out, padding-bottom 0.3s ease-out;
        padding-top: 0;
        padding-bottom: 0;
    }

    .submenu li a {
        font-size: 0.95em;
        padding: 10px 15px;
        margin: 0 5px;
    }

    .menu-icon {
        margin-right: 10px;
        font-size: 1.1em;
        color: #95a5a6;
        transition: color 0.3s ease;
    }

    .main-menu > li > a:hover .menu-icon, .submenu > li > a:hover .menu-icon {
        color: #ffffff;
    }

    .submenu-caret {
        margin-left: auto;
        transition: transform 0.3s ease;
        font-size: 0.8em;
        color: #bdc3c7;
    }

    .has-submenu.active > .submenu {
        display: block;
        max-height: 500px;
        padding-top: 5px;
        padding-bottom: 5px;
    }

    .has-submenu.active > a .submenu-caret {
        transform: rotate(180deg);
    }

    .no-menu-available {
        text-align: center;
        padding: 20px;
        color: #bdc3c7;
        font-size: 0.9em;
        opacity: 0.7;
    }

    @media (max-width: 768px) {
        .sidebar-nav {
            width: 100%;
            max-height: none;
            border-radius: 0;
        }
        .main-menu > li > a, .submenu > li > a {
            padding: 10px 15px;
            margin: 0 5px;
            font-size: 0.9em;
        }
        .menu-icon {
            font-size: 1em;
            margin-right: 8px;
        }
    }
</style>

<script>
    // JavaScript, inchangé
    document.addEventListener('DOMContentLoaded', function() {
        const hasSubmenuLinks = document.querySelectorAll('.has-submenu > a');

        hasSubmenuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const parentLi = this.closest('li');
                const submenu = parentLi.querySelector('.submenu');

                if (this.getAttribute('href') === '#' || (submenu && !this.getAttribute('href').startsWith('/'))) {
                    e.preventDefault();

                    const siblingLis = parentLi.parentNode.children;
                    Array.from(siblingLis).forEach(siblingLi => {
                        if (siblingLi !== parentLi && siblingLi.classList.contains('has-submenu') && siblingLi.classList.contains('active')) {
                            siblingLi.classList.remove('active');
                        }
                    });

                    parentLi.classList.toggle('active');
                }
            });
        });

        document.addEventListener('click', function(e) {
            const sidebarNav = document.querySelector('.sidebar-nav');
            if (sidebarNav && !sidebarNav.contains(e.target)) {
                document.querySelectorAll('.has-submenu.active').forEach(item => {
                    item.classList.remove('active');
                });
            }
        });
    });
</script>
