<?php
/**
 * Menu latéral modernisé - GestionMySoutenance
 * Navigation principale avec données de la base de données
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

    // Gérer explicitement l'URL racine
    if ($url === '' || $url === '/') {
        return $current === '' || $current === '/';
    }

    // Pour une correspondance exacte, en particulier pour les pages feuille
    if ($current === $url) {
        return true;
    }

    // Pour une correspondance partielle (par exemple, un élément de menu parent)
    // Assurez-vous qu'il correspond à un segment complet pour éviter les faux positifs (par exemple, /user correspondant à /users)
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
        // Ajoutez d'autres mappings si nécessaire en fonction de vos icônes de DB
        'fas fa-chart-line' => 'show_chart', // Pour Reporting
        'fas fa-calendar-alt' => 'event', // Pour Année Académique
        'fas fa-bell' => 'notifications', // Pour Notifications
        'fas fa-sliders-h' => 'tune', // Pour Paramètres Généraux
        'fas fa-folder' => 'folder', // Pour Gestion Fichiers
        'fas fa-list' => 'list', // Pour Lister Fichiers
        'fas fa-upload' => 'cloud_upload', // Pour Uploader Fichier
        'fas fa-graduation-cap' => 'school', // Pour Gestion Académique
        'fas fa-book' => 'book', // Pour ECUEs
        'fas fa-user-plus' => 'person_add', // Pour Inscriptions
        'fas fa-clipboard-list' => 'assignment', // Pour Notes
        'fas fa-briefcase' => 'business_center', // Pour Stages
        'fas fa-university' => 'account_balance', // Pour UEs
        'fas fa-chalkboard-teacher' => 'school', // Pour Carrières Enseignants
        'fas fa-shield-alt' => 'security', // Pour Habilitations
        'fas fa-users-cog' => 'people_alt', // Pour Gestion Groupes
        'fas fa-lock' => 'lock', // Pour Niveaux Accès
        'fas fa-link' => 'link', // Pour Gestion Rattachements
        'fas fa-book-open' => 'menu_book', // Pour Référentiels
        'fas fa-list-alt' => 'list_alt', // Pour Lister Référentiels
        'fas fa-edit' => 'edit', // Pour CRUD Référentiel
        'fas fa-history' => 'history', // Pour Journaux Audit
        'fas fa-file-code' => 'code', // Pour Logs Système
        'fas fa-tools' => 'handyman', // Pour Maintenance
        'fas fa-tasks' => 'task', // Pour Queue Tâches
        'fas fa-project-diagram' => 'account_tree', // Pour Suivi Workflows
        'fas fa-exchange-alt' => 'swap_horiz', // Pour Transition de Rôle
        'fas fa-user-tag' => 'label_important', // Pour Gestion Délégations
        'fas fa-file-import' => 'upload_file', // Pour Import Étudiants
        'fas fa-user-circle' => 'account_circle', // Mon Profil
        'fas fa-file-upload' => 'upload_file', // Gestion Rapport
        'fas fa-exclamation-triangle' => 'warning', // Gestion Réclamation
        'fas fa-paper-plane' => 'send', // Soumettre Réclamation
        'fas fa-check-circle' => 'check_circle', // Conformité
        'fas fa-clipboard-check' => 'rule', // Rapports à Vérifier
        'fas fa-check-double' => 'done_all', // Rapports Traités
        'fas fa-file-invoice' => 'receipt_long', // Documents Admin
        'fas fa-file-export' => 'download', // Génération Documents
        'fas fa-balance-scale-right' => 'gavel', // Gestion Pénalités
        'fas fa-vote-yea' => 'how_to_vote', // Interface Vote
        'fas fa-pen' => 'edit', // Rédiger PV
        'fas fa-search' => 'search', // Consulter PV
        'fas fa-file-signature' => 'description', // Gestion PV
        'fas fa-file-contract' => 'description', // Gestion Rapports
        'fas fa-info-circle' => 'info' // Détails Rapport
    ];

    return $iconMap[$faIcon] ?? 'circle';
}

// Fonction generateUrl() et $urlMap ont été supprimées car les URLs viennent maintenant de la DB.

// Informations utilisateur pour la sidebar
$user_name = '';
if ($current_user) {
    $user_name = trim(($current_user['nom'] ?? '') . ' ' . ($current_user['prenom'] ?? ''));
    if (empty($user_name)) {
        $user_name = $current_user['login_utilisateur'] ?? 'Utilisateur';
    }
}

$user_initials = '';
if (!empty($user_name)) {
    $name_parts = explode(' ', trim($user_name));
    $user_initials = strtoupper(substr($name_parts[0], 0, 1));
    if (isset($name_parts[1])) {
        $user_initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
}

$role_display = [
    'admin' => 'Administrateur',
    'enseignant' => 'Enseignant',
    'etudiant' => 'Étudiant',
    'personnel' => 'Personnel'
];
$user_role_display = $role_display[$user_role] ?? ucfirst($user_role);

// Les logs de debug ont été supprimés.
?>

<aside class="gestionsoutenance-sidebar" id="sidebar">
    <div class="sidebar-content">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <span class="material-icons">school</span>
            </div>
            <span class="brand-text hide-when-collapsed">GestionMySoutenance</span>
        </div>

        <?php if ($user_role !== 'guest' && $current_user): ?>
            <div class="user-info hide-when-collapsed">
                <div class="user-avatar">
                    <?= e($user_initials) ?>
                </div>
                <div class="user-details">
                    <p class="user-name"><?= e($user_name) ?></p>
                    <p class="user-role"><?= e($user_role_display) ?></p>
                    <?php if (!empty($current_user['email_principal'])): ?>
                        <p class="user-email"><?= e($current_user['email_principal']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <nav class="sidebar-nav">
            <?php if (!empty($menu_items)): ?>
                <?php foreach ($menu_items as $item): ?>
                    <?php
                    // Vérifier si l'utilisateur a la permission pour cet élément
                    // $user_permissions contient les id_traitement du groupe et des délégations
                    if (!hasPermission($item['id_traitement'], $user_permissions)) {
                        continue;
                    }

                    // L'URL vient de la base de données via $item['url_associee']
                    $url = $item['url_associee'];
                    $icon = convertIconToMaterial($item['icone_class']);
                    $label = $item['libelle_menu']; // libelle_traitement est récupéré comme libelle_menu
                    $isActive = isActive($url, $current_url);
                    ?>

                    <?php if (!empty($item['enfants'])): ?>
                        <div class="collapsible-menu" data-section="<?= e($item['id_traitement']) ?>">
                            <div class="collapsible-header">
                                <div class="nav-item-content">
                                    <span class="material-icons"><?= e($icon) ?></span>
                                    <span class="hide-when-collapsed"><?= e($label) ?></span>
                                </div>
                                <span class="material-icons expand-icon hide-when-collapsed">chevron_right</span>
                            </div>

                            <div class="collapsible-content">
                                <?php foreach ($item['enfants'] as $child): ?>
                                    <?php
                                    // Vérifier les permissions pour l'enfant
                                    if (!hasPermission($child['id_traitement'], $user_permissions)) {
                                        continue;
                                    }

                                    // L'URL de l'enfant vient aussi de la base de données
                                    $childUrl = $child['url_associee'];
                                    $childIcon = convertIconToMaterial($child['icone_class']);
                                    $childLabel = $child['libelle_menu'];
                                    $childIsActive = isActive($childUrl, $current_url);
                                    ?>
                                    <a href="<?= e($childUrl) ?>"
                                       class="nav-item <?= $childIsActive ? 'active' : '' ?>">
                                        <span class="material-icons"><?= e($childIcon) ?></span>
                                        <span class="hide-when-collapsed"><?= e($childLabel) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= e($url) ?>"
                           class="nav-item <?= $isActive ? 'active' : '' ?>">
                            <span class="material-icons"><?= e($icon) ?></span>
                            <span class="hide-when-collapsed"><?= e($label) ?></span>
                        </a>
                    <?php endif; ?>
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
                <span class="hide-when-collapsed">Déconnexion</span>
            </a>
        </div>
    </div>
</aside>

<style>
    /* Styles pour le menu */
    .gestionsoutenance-sidebar {
        width: 280px;
        background: #ffffff;
        border-right: 1px solid #e5e5e5;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        transition: all 0.3s ease;
        overflow-y: auto;
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
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
        font-size: 14px;
    }

    .user-details {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        margin: 0 0 4px 0;
        color: #333;
        font-size: 14px;
    }

    .user-role {
        font-size: 12px;
        color: #666;
        margin: 0 0 2px 0;
    }

    .user-email {
        font-size: 11px;
        color: #999;
        margin: 0;
    }

    .sidebar-nav {
        flex: 1;
    }

    .collapsible-menu {
        margin-bottom: 5px;
    }

    .collapsible-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        cursor: pointer;
        border-radius: 6px;
        transition: background-color 0.2s;
    }

    .collapsible-header:hover {
        background-color: #f8f9fa;
    }

    .nav-item-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .expand-icon {
        font-size: 18px;
        transition: transform 0.2s;
    }

    .collapsible-menu.expanded .expand-icon {
        transform: rotate(90deg);
    }

    .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .collapsible-menu.expanded .collapsible-content {
        max-height: 500px;
    }

    .collapsible-content .nav-item {
        padding-left: 52px;
        border-radius: 6px;
        margin: 2px 0;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        text-decoration: none;
        color: #333;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 14px;
    }

    .nav-item:hover {
        background-color: #f8f9fa;
        color: #007bff;
        text-decoration: none;
    }

    .nav-item.active {
        background-color: #007bff;
        color: white;
    }

    .nav-item.active .material-icons {
        color: white;
    }

    .nav-item .material-icons {
        font-size: 20px;
    }

    .no-menu-message {
        text-align: center;
        padding: 20px;
        color: #666;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .sidebar-footer {
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #e5e5e5;
    }

    .logout-item {
        color: #dc3545;
    }

    .logout-item:hover {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .gestionsoutenance-sidebar {
            width: 60px;
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
    }
</style>

<script>
    // JavaScript pour le menu collapsible
    document.addEventListener('DOMContentLoaded', function() {
        const collapsibleMenus = document.querySelectorAll('.collapsible-menu');

        collapsibleMenus.forEach(menu => {
            const header = menu.querySelector('.collapsible-header');

            if (header) {
                header.addEventListener('click', function() {
                    menu.classList.toggle('expanded');
                });
            }
        });

        // Ouvrir automatiquement le menu contenant la page active
        const activeItems = document.querySelectorAll('.nav-item.active');
        activeItems.forEach(item => {
            const parentMenu = item.closest('.collapsible-menu');
            if (parentMenu) {
                parentMenu.classList.add('expanded');
            }
        });

        console.log('Menu initialisé avec', collapsibleMenus.length, 'menus collapsibles');
    });
</script>