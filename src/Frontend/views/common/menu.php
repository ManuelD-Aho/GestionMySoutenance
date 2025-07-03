<?php
// src/Frontend/views/common/menu.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Récupérer le rôle/les permissions de l'utilisateur courant pour construire le menu dynamiquement
// Assumons que $_SESSION['user_permissions_codes'] contient un tableau des codes de permissions (ex: TRAIT_ADMIN_DASHBOARD_ACCESS)
// et $_SESSION['user_role_code'] contient le code du groupe principal (ex: GRP_ADMIN)
$user_permissions = $_SESSION['user_permissions_codes'] ?? [];
$user_role_code = $_SESSION['user_role_code'] ?? 'GRP_ETUDIANT'; // Rôle par défaut pour l'exemple

// Définition de la structure du menu
// Chaque item : ['label', 'icon', 'url', 'required_permission', 'roles_allowed', 'children']
$menu_items = [
    // Dashboard général (commun à tous)
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => '/dashboard', 'required_permission' => null, 'roles_allowed' => ['ALL']],

    // Module Administration Générale
    [
        'label' => 'Administration', 'icon' => 'admin_panel_settings', 'url' => '#',
        'required_permission' => 'TRAIT_ADMIN_DASHBOARD_ACCESS', // Ex: Seul l'admin y a accès
        'roles_allowed' => ['GRP_ADMIN'],
        'children' => [
            ['label' => 'Tableau de Bord Admin', 'icon' => 'dashboard', 'url' => '/admin/dashboard', 'required_permission' => 'TRAIT_ADMIN_DASHBOARD_ACCESS'],
            ['label' => 'Gestion des Utilisateurs', 'icon' => 'group', 'url' => '/admin/utilisateurs/liste', 'required_permission' => 'TRAIT_USER_MANAGE'],
            ['label' => 'Configuration Système', 'icon' => 'settings', 'url' => '/admin/config/parametres-generaux', 'required_permission' => 'TRAIT_CONFIG_MANAGE'],
            ['label' => 'Habilitations (RBAC)', 'icon' => 'security', 'url' => '/admin/habilitations', 'required_permission' => 'TRAIT_RBAC_MANAGE'],
            ['label' => 'Gestion Académique', 'icon' => 'school', 'url' => '/admin/gestion-acad', 'required_permission' => 'TRAIT_ACADEMIC_MANAGE'],
            ['label' => 'Gestion des Fichiers', 'icon' => 'folder_open', 'url' => '/admin/fichiers/list', 'required_permission' => 'TRAIT_FILE_MANAGE'],
            ['label' => 'Référentiels', 'icon' => 'list_alt', 'url' => '/admin/referentiels/liste', 'required_permission' => 'TRAIT_REFERENTIAL_MANAGE'],
            ['label' => 'Supervision & Logs', 'icon' => 'monitor', 'url' => '/admin/supervision', 'required_permission' => 'TRAIT_SUPERVISION_ACCESS'],
            ['label' => 'Délégations & Transitions', 'icon' => 'swap_horiz', 'url' => '/admin/transition-role', 'required_permission' => 'TRAIT_DELEGATION_MANAGE'],
        ]
    ],

    // Module Commission
    [
        'label' => 'Commission', 'icon' => 'gavel', 'url' => '#',
        'required_permission' => 'TRAIT_COMMISSION_ACCESS',
        'roles_allowed' => ['GRP_COMMISSION', 'GRP_ADMIN', 'GRP_ENSEIGNANT'], // Un enseignant peut aussi être membre
        'children' => [
            ['label' => 'Tableau de Bord', 'icon' => 'dashboard', 'url' => '/commission/dashboard', 'required_permission' => 'TRAIT_COMMISSION_ACCESS'],
            ['label' => 'Rapports à Traiter', 'icon' => 'assignment', 'url' => '/commission/rapports/liste', 'required_permission' => 'TRAIT_COMMISSION_EVALUATE'],
            ['label' => 'Corrections Rapports', 'icon' => 'assignment_turned_in', 'url' => '/commission/corrections', 'required_permission' => 'TRAIT_COMMISSION_EVALUATE'],
            ['label' => 'PV à Valider', 'icon' => 'description', 'url' => '/commission/pv/liste-a-valider', 'required_permission' => 'TRAIT_PV_VALIDER'],
            ['label' => 'Rédiger PV', 'icon' => 'edit_note', 'url' => '/commission/pv/rediger', 'required_permission' => 'TRAIT_PV_REDIGER'],
            ['label' => 'Historique Commission', 'icon' => 'history', 'url' => '/commission/historique', 'required_permission' => 'TRAIT_COMMISSION_HISTORY_ACCESS'],
            ['label' => 'Messagerie Interne', 'icon' => 'chat', 'url' => '/chat', 'required_permission' => 'TRAIT_MESSAGERIE_ACCESS'],
        ]
    ],

    // Module Étudiant
    [
        'label' => 'Espace Étudiant', 'icon' => 'person', 'url' => '#',
        'required_permission' => 'TRAIT_ETUDIANT_DASHBOARD_ACCESS',
        'roles_allowed' => ['GRP_ETUDIANT'],
        'children' => [
            ['label' => 'Mon Tableau de Bord', 'icon' => 'dashboard', 'url' => '/etudiant/dashboard', 'required_permission' => 'TRAIT_ETUDIANT_DASHBOARD_ACCESS'],
            ['label' => 'Soumettre mon Rapport', 'icon' => 'upload_file', 'url' => '/etudiant/rapport/soumettre', 'required_permission' => 'TRAIT_RAPPORT_SOUMETTRE'],
            ['label' => 'Suivi de mon Rapport', 'icon' => 'track_changes', 'url' => '/etudiant/rapport/suivi', 'required_permission' => 'TRAIT_RAPPORT_SUIVI'],
            ['label' => 'Mes Documents', 'icon' => 'folder_shared', 'url' => '/etudiant/documents', 'required_permission' => 'TRAIT_DOCUMENT_VIEW'],
            ['label' => 'Mon Profil', 'icon' => 'account_circle', 'url' => '/etudiant/profile', 'required_permission' => 'TRAIT_PROFILE_VIEW'],
            ['label' => 'Mes Réclamations', 'icon' => 'feedback', 'url' => '/etudiant/reclamations', 'required_permission' => 'TRAIT_RECLAMATION_VIEW'],
            ['label' => 'Ressources & Aide', 'icon' => 'help', 'url' => '/etudiant/ressources', 'required_permission' => 'TRAIT_RESSOURCE_VIEW'],
        ]
    ],

    // Module Personnel Administratif (ex: RS, Agent Conformité)
    [
        'label' => 'Personnel Admin.', 'icon' => 'how_to_reg', 'url' => '#',
        'required_permission' => 'TRAIT_PERSONNEL_DASHBOARD_ACCESS',
        'roles_allowed' => ['GRP_RS', 'GRP_AGENT_CONFORMITE'],
        'children' => [
            ['label' => 'Tableau de Bord', 'icon' => 'dashboard', 'url' => '/personnel/dashboard', 'required_permission' => 'TRAIT_PERSONNEL_DASHBOARD_ACCESS'],
            ['label' => 'Contrôle Conformité', 'icon' => 'rule', 'url' => '/personnel/conformite/rapports-a-verifier', 'required_permission' => 'TRAIT_RAPPORT_CONFORMITE_VERIFY'],
            ['label' => 'Gestion Scolarité', 'icon' => 'school', 'url' => '/personnel/scolarite', 'required_permission' => 'TRAIT_SCOLARITE_MANAGE'],
            ['label' => 'Génération Documents', 'icon' => 'picture_as_pdf', 'url' => '/personnel/documents/generate', 'required_permission' => 'TRAIT_DOCUMENT_GENERATE'],
            ['label' => 'Messagerie Interne', 'icon' => 'chat', 'url' => '/chat', 'required_permission' => 'TRAIT_MESSAGERIE_ACCESS'],
        ]
    ],

    // Sécurité et Paramètres généraux (souvent pour Admin)
    ['label' => 'Paramètres Généraux', 'icon' => 'settings', 'url' => '/settings', 'required_permission' => 'TRAIT_GENERIC_SETTINGS_ACCESS', 'roles_allowed' => ['GRP_ADMIN']],
    ['label' => 'Mon Profil', 'icon' => 'account_circle', 'url' => '/profile', 'required_permission' => null, 'roles_allowed' => ['ALL']],
    ['label' => 'Aide & Support', 'icon' => 'help', 'url' => '/help', 'required_permission' => null, 'roles_allowed' => ['ALL']],
];

// Fonction d'aide pour vérifier si un item de menu doit être affiché
function shouldDisplayMenuItem($item, $user_role_code, $user_permissions) {
    // Si la permission est 'null' ou 'ALL', l'item est affiché pour tout le monde ou rôle spécifique
    if ($item['required_permission'] === null) {
        return true;
    }
    // Si un rôle spécifique est défini et l'utilisateur a ce rôle
    if (isset($item['roles_allowed']) && in_array($user_role_code, $item['roles_allowed'])) {
        return true;
    }
    // Si l'utilisateur a la permission requise
    if (in_array($item['required_permission'], $user_permissions)) {
        return true;
    }
    return false;
}

// Fonction récursive pour rendre les items de menu
function renderMenuItems($items, $user_role_code, $user_permissions, $current_url) {
    echo '<ul class="menu-list">';
    foreach ($items as $item) {
        // Vérifier les permissions avant d'afficher l'élément de menu
        if (!shouldDisplayMenuItem($item, $user_role_code, $user_permissions)) {
            continue;
        }

        $has_children = isset($item['children']) && !empty($item['children']);
        $is_active = ($item['url'] === $current_url || ($has_children && strpos($current_url, $item['url']) === 0)); // Actif si URL correspond ou si un enfant correspond
        $is_parent_active = $has_children && strpos($current_url, $item['url']) === 0;

        // Pour les parents, vérifier si au moins un enfant est affichable
        if ($has_children) {
            $has_displayable_children = false;
            foreach ($item['children'] as $child) {
                if (shouldDisplayMenuItem($child, $user_role_code, $user_permissions)) {
                    $has_displayable_children = true;
                    break;
                }
            }
            if (!$has_displayable_children) {
                continue; // Ne pas afficher le parent s'il n'a pas d'enfants affichables
            }
        }

        $link_class = 'menu-link ' . ($is_active ? 'active' : '');
        $item_class = 'menu-item ' . ($has_children ? 'has-submenu' : '') . ($is_parent_active ? 'active-parent' : ''); // active-parent pour style du parent
        $arrow_icon = $has_children ? '<span class="material-icons menu-arrow">chevron_right</span>' : '';

        echo '<li class="' . $item_class . '">';
        echo '<a href="' . e($item['url']) . '" class="' . $link_class . '">';
        echo '<span class="material-icons menu-icon">' . e($item['icon']) . '</span>';
        echo '<span class="menu-label">' . e($item['label']) . '</span>';
        echo $arrow_icon;
        echo '</a>';

        if ($has_children) {
            renderMenuItems($item['children'], $user_role_code, $user_permissions, $current_url); // Récursion pour les sous-menus
        }
        echo '</li>';
    }
    echo '</ul>';
}

// Récupérer l'URL courante pour marquer l'élément actif
$current_url = $_SERVER['REQUEST_URI'];
// Nettoyer l'URL pour la comparaison (ex: retirer les paramètres GET)
$current_url = strtok($current_url, '?');

// Informations de l'utilisateur pour le profil dans le menu
$currentUser = $_SESSION['user_data'] ?? null;
$userName = 'Utilisateur';
$userRole = 'Rôle Inconnu';
$userAvatar = null;

if ($currentUser) {
    $userName = htmlspecialchars($currentUser['prenom'] ?? '') . ' ' . htmlspecialchars($currentUser['nom'] ?? '');
    $userAvatar = htmlspecialchars($currentUser['photo_profil'] ?? '');
    $userRole = htmlspecialchars($_SESSION['user_role_label'] ?? ($currentUser['id_type_utilisateur'] ?? 'Rôle Inconnu'));
}
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <a href="/dashboard">
                <img src="/assets/img/logo.png" alt="Logo GestionMySoutenance" class="logo-img">
                <h1>GestionMySoutenance</h1>
            </a>
        </div>
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
            <span class="material-icons">chevron_left</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        <?php renderMenuItems($menu_items, $user_role_code, $user_permissions, $current_url); ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile-sidebar">
            <div class="user-avatar">
                <?php if ($userAvatar): ?>
                    <img src="<?php echo $userAvatar; ?>" alt="Avatar de <?php echo $userName; ?>">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="status-indicator online"></div>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo $userName; ?></div>
                <div class="user-role"><?php echo $userRole; ?></div>
            </div>
            <a href="/logout" class="logout-btn" title="Déconnexion">
                <span class="material-icons">logout</span>
            </a>
        </div>
    </div>
</aside>