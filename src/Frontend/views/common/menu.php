<?php
/**
 * Menu latéral avec support complet de 3 niveaux - GestionMySoutenance
 * Navigation hiérarchique récursive
 */

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Récupération des données depuis le contrôleur
$menu_items = $menu_items ?? $data['menu_items'] ?? [];
$user_permissions = $user_permissions ?? $data['user_permissions'] ?? $_SESSION['user_group_permissions'] ?? [];
$user_role = $user_role ?? $data['user_role'] ?? 'guest';
$current_user = $current_user ?? $data['user'] ?? $_SESSION['user_data'] ?? null;
$current_url = $current_url ?? $_SERVER['REQUEST_URI'];

// Fonction helper pour vérifier les permissions
function hasPermission($permission, $user_permissions) {
    return in_array($permission, $user_permissions) || in_array('*', $user_permissions);
}

function isActive($url, $current_url) {
    if (empty($url)) return false;

    $url = rtrim($url, '/');
    $current = rtrim(strtok($current_url, '?'), '/');

    if ($url === '' || $url === '/') {
        return $current === '' || $current === '/';
    }

    if ($current === $url) {
        return true;
    }

    return strpos($current . '/', $url . '/') === 0;
}

// Fonction pour convertir l'icône Font Awesome en Material Icons
function convertIconToMaterial($faIcon) {
    $iconMap = [
        'fas fa-tachometer-alt' => 'dashboard',
        'fas fa-user-graduate' => 'school',
        'fas fa-gavel' => 'gavel',
        'fas fa-user-tie' => 'work',
        'fas fa-cogs' => 'settings',
        'fas fa-users' => 'people',
        'fas fa-file-alt' => 'description',
        'fas fa-chart-bar' => 'assessment',
        'fas fa-shield-alt' => 'security',
        'fas fa-wrench' => 'build',
        'fas fa-eye' => 'visibility',
        'fas fa-chart-line' => 'show_chart',
        'fas fa-calendar-alt' => 'event',
        'fas fa-bell' => 'notifications',
        'fas fa-sliders-h' => 'tune',
        'fas fa-folder' => 'folder',
        'fas fa-list' => 'list',
        'fas fa-upload' => 'cloud_upload',
        'fas fa-graduation-cap' => 'school',
        'fas fa-book' => 'book',
        'fas fa-user-plus' => 'person_add',
        'fas fa-clipboard-list' => 'assignment',
        'fas fa-briefcase' => 'work',
        'fas fa-university' => 'account_balance',
        'fas fa-chalkboard-teacher' => 'person',
        'fas fa-users-cog' => 'admin_panel_settings',
        'fas fa-lock' => 'lock',
        'fas fa-link' => 'link',
        'fas fa-user-tag' => 'badge',
        'fas fa-edit' => 'edit',
        'fas fa-list-alt' => 'view_list',
        'fas fa-book-open' => 'menu_book',
        'fas fa-history' => 'history',
        'fas fa-file-code' => 'code',
        'fas fa-tools' => 'build',
        'fas fa-tasks' => 'task_alt',
        'fas fa-project-diagram' => 'account_tree',
        'fas fa-exchange-alt' => 'swap_horiz',
        'fas fa-user-edit' => 'edit'
    ];

    return $iconMap[$faIcon] ?? 'circle';
}

/**
 * Fonction récursive pour rendre les éléments de menu
 * Support illimité de niveaux hiérarchiques
 */
function renderMenuItemRecursive($item, $user_permissions, $current_url, $level = 1) {
    // Vérifier les permissions
    if (!hasPermission($item['id_traitement'], $user_permissions)) {
        return '';
    }

    $url = $item['url_associee'] ?? '';
    $icon = convertIconToMaterial($item['icone_class'] ?? '');
    $label = $item['libelle_menu'] ?? $item['libelle_traitement'] ?? 'Menu';
    $isActive = !empty($url) && isActive($url, $current_url);
    $hasChildren = !empty($item['enfants']);

    // Calculer les classes CSS et le padding selon le niveau
    $baseItemClass = $level === 1 ? 'nav-item' : 'submenu-item';
    $paddingLeft = 16 + (($level - 1) * 24); // Indentation progressive
    $containerClass = 'collapsible-menu';
    $headerClass = 'collapsible-header';

    // Classes spéciales pour les niveaux profonds
    if ($level >= 3) {
        $baseItemClass .= ' deep-level';
        $containerClass .= ' deep-menu';
    }

    $html = '';

    if ($hasChildren) {
        // Élément conteneur avec sous-éléments
        $html .= '<div class="' . $containerClass . '" data-level="' . $level . '" data-section="' . e($item['id_traitement']) . '">';
        $html .= '<div class="' . $headerClass . '" style="padding-left: ' . $paddingLeft . 'px;">';
        $html .= '<div class="nav-item-content">';
        $html .= '<span class="material-icons">' . e($icon) . '</span>';
        $html .= '<span class="menu-label hide-when-collapsed">' . e($label) . '</span>';
        $html .= '</div>';
        $html .= '<span class="material-icons expand-icon hide-when-collapsed">chevron_right</span>';
        $html .= '</div>';

        $html .= '<div class="collapsible-content">';

        // Rendre récursivement tous les enfants
        foreach ($item['enfants'] as $child) {
            $html .= renderMenuItemRecursive($child, $user_permissions, $current_url, $level + 1);
        }

        $html .= '</div>';
        $html .= '</div>';

    } else {
        // Élément simple avec URL
        if (empty($url)) {
            return ''; // Ignorer les éléments sans URL
        }

        $activeClass = $isActive ? ' active' : '';
        $html .= '<a href="' . e($url) . '" class="' . $baseItemClass . $activeClass . '" ';
        $html .= 'style="padding-left: ' . $paddingLeft . 'px;" data-level="' . $level . '">';
        $html .= '<span class="material-icons">' . e($icon) . '</span>';
        $html .= '<span class="menu-label hide-when-collapsed">' . e($label) . '</span>';
        $html .= '</a>';
    }

    return $html;
}

/**
 * Fonction pour trouver si un menu contient une page active (récursive)
 */
function menuContainsActivePage($item, $current_url) {
    // Vérifier l'élément actuel
    if (!empty($item['url_associee']) && isActive($item['url_associee'], $current_url)) {
        return true;
    }

    // Vérifier récursivement les enfants
    if (!empty($item['enfants'])) {
        foreach ($item['enfants'] as $child) {
            if (menuContainsActivePage($child, $current_url)) {
                return true;
            }
        }
    }

    return false;
}
?>

<aside class="gestionsoutenance-sidebar" id="sidebar">
    <div class="sidebar-content">
        <!-- En-tête avec logo -->
        <div class="sidebar-brand">
            <div class="brand-logo">
                <span class="material-icons">school</span>
            </div>
            <div class="brand-text hide-when-collapsed">GestionMySoutenance</div>
        </div>


        <!-- Informations utilisateur -->
        <?php if ($current_user): ?>
            <div class="user-info hide-when-collapsed">
                <div class="user-avatar">
                    <span class="material-icons">person</span>
                </div>
                <div class="user-details">
                    <h4><?= e($current_user['prenom'] ?? 'Utilisateur') ?> <?= e($current_user['nom'] ?? '') ?></h4>
                    <small><?= e($user_role) ?></small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Navigation principale -->
        <nav class="sidebar-nav">
            <?php if (!empty($menu_items)): ?>
                <?php foreach ($menu_items as $item): ?>
                    <?= renderMenuItemRecursive($item, $user_permissions, $current_url, 1) ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-menu-message">
                    <p>Aucun menu disponible.</p>
                    <small>Permissions: <?= count($user_permissions) ?> trouvées</small>
                </div>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout" class="nav-item logout-item">
                <span class="material-icons">logout</span>
                <span class="menu-label hide-when-collapsed">Déconnexion</span>
            </a>
        </div>
    </div>


</aside>

<style>
    /* Styles pour le menu à 3 niveaux */
    .gestionsoutenance-sidebar {
        width: 320px;
        background: #ffffff;
        border-right: 1px solid #e5e5e5;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        transition: all 0.3s ease;
        overflow-y: auto;
        box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    }

    .sidebar-content {
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5;
    }

    .brand-logo .material-icons {
        font-size: 32px;
        color: #007bff;
        margin-right: 12px;
    }

    .brand-text {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }

    .user-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }

    .user-avatar .material-icons {
        color: white;
        font-size: 20px;
    }

    .user-details h4 {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }

    .user-details small {
        color: #666;
        font-size: 12px;
    }

    .sidebar-nav {
        flex: 1;
        padding-right: 5px; /* Pour éviter la coupure du scrollbar */
    }

    /* Styles de base pour les éléments de menu */
    .nav-item, .submenu-item {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        margin-bottom: 2px;
        text-decoration: none;
        color: #333;
        border-radius: 6px;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 14px;
        position: relative;
    }

    /* Styles spécifiques selon le niveau */
    .nav-item {
        font-weight: 500;
    }

    .submenu-item {
        font-size: 13px;
        color: #555;
        font-weight: 400;
    }

    .submenu-item.deep-level {
        font-size: 12px;
        color: #666;
        background: rgba(248, 249, 250, 0.5);
    }

    /* États hover */
    .nav-item:hover, .submenu-item:hover {
        background-color: #f8f9fa;
        text-decoration: none;
        color: #007bff;
        transform: translateX(2px);
    }

    /* États actifs */
    .nav-item.active, .submenu-item.active {
        background-color: #007bff;
        color: white;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    }

    .nav-item.active .material-icons,
    .submenu-item.active .material-icons {
        color: white;
    }

    /* Icônes */
    .nav-item .material-icons,
    .submenu-item .material-icons {
        font-size: 18px;
        margin-right: 12px;
        width: 18px;
        flex-shrink: 0;
    }

    .nav-item .material-icons {
        font-size: 20px;
        width: 20px;
    }

    /* Styles pour les menus collapsibles */
    .collapsible-menu {
        margin-bottom: 3px;
    }

    .collapsible-menu.deep-menu {
        border-left: 2px solid #e9ecef;
        margin-left: 10px;
        margin-bottom: 2px;
    }

    .collapsible-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.2s ease;
        color: #333;
        font-size: 14px;
        font-weight: 500;
    }

    .collapsible-header:hover {
        background-color: #f8f9fa;
        color: #007bff;
        transform: translateX(2px);
    }

    .nav-item-content {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .nav-item-content .material-icons {
        margin-right: 12px;
        font-size: 18px;
        width: 18px;
    }

    .menu-label {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .expand-icon {
        transition: transform 0.3s ease;
        font-size: 18px !important;
        width: 18px !important;
        margin-right: 0 !important;
        margin-left: 8px;
        flex-shrink: 0;
    }

    .collapsible-menu.expanded .expand-icon {
        transform: rotate(90deg);
    }

    /* Animation pour le contenu collapsible */
    .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, padding 0.3s ease;
    }

    .collapsible-menu.expanded .collapsible-content {
        max-height: 2000px;
    }

    /* Indicateurs visuels pour les niveaux profonds */
    .collapsible-menu[data-level="3"] .collapsible-header {
        background: rgba(248, 249, 250, 0.8);
        border-left: 3px solid #dee2e6;
    }

    .sidebar-footer {
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #e5e5e5;
    }

    .logout-item {
        color: #dc3545;
        font-weight: 500;
    }

    .logout-item:hover {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        transform: translateX(2px);
    }

    /* Responsive - Mode réduit */
    @media (max-width: 768px) {
        .gestionsoutenance-sidebar {
            width: 70px;
        }

        .hide-when-collapsed {
            display: none;
        }

        .user-info {
            display: none;
        }

        .sidebar-brand .brand-text {
            display: none;
        }

        .nav-item, .submenu-item {
            padding: 12px 8px;
            justify-content: center;
        }

        .collapsible-header {
            padding: 12px 8px;
            justify-content: center;
        }
    }

    /* Scrollbar personnalisée pour webkit */
    .gestionsoutenance-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .gestionsoutenance-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .gestionsoutenance-sidebar::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .gestionsoutenance-sidebar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<script>
    // JavaScript pour le menu collapsible à 3 niveaux
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initialisation du menu à 3 niveaux...');

        const collapsibleMenus = document.querySelectorAll('.collapsible-menu');
        console.log('Menus collapsibles trouvés:', collapsibleMenus.length);

        // Attacher les événements de clic
        collapsibleMenus.forEach(menu => {
            const header = menu.querySelector('.collapsible-header');

            if (header) {
                header.addEventListener('click', function(e) {
                    e.stopPropagation();

                    // Toggle de l'état expanded
                    menu.classList.toggle('expanded');

                    console.log('Menu toggled:', menu.dataset.section, 'expanded:', menu.classList.contains('expanded'));
                });
            }
        });

        // Fonction pour expanser récursivement les parents d'un élément
        function expandParents(element) {
            let parent = element.closest('.collapsible-menu');
            while (parent) {
                parent.classList.add('expanded');
                console.log('Parent étendu:', parent.dataset.section);

                // Chercher le parent du parent
                parent = parent.parentElement.closest('.collapsible-menu');
            }
        }

        // Ouvrir automatiquement les menus contenant la page active
        function expandActiveMenus() {
            const activeItems = document.querySelectorAll('.nav-item.active, .submenu-item.active');
            console.log('Éléments actifs trouvés:', activeItems.length);

            activeItems.forEach(item => {
                expandParents(item);
            });
        }

        // Appliquer l'expansion automatique
        expandActiveMenus();

        // Fonction pour basculer le mode réduit (pour utilisation future)
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        };

        console.log('Menu à 3 niveaux initialisé avec succès !');
    });
</script>