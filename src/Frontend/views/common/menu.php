<?php
/**
 * Menu latéral modernisé - GestionMySoutenance
 * Navigation principale avec permissions et rôles
 */

// Récupération des données utilisateur et permissions
$user_role = $user_role ?? $_SESSION['user_role'] ?? 'guest';
$user_permissions = $user_permissions ?? $_SESSION['user_permissions'] ?? [];
$current_url = $current_url ?? $_SERVER['REQUEST_URI'];

// Fonction helper pour vérifier les permissions
function hasPermission($permission, $user_permissions) {
    return in_array($permission, $user_permissions) || in_array('*', $user_permissions);
}

function hasRole($role, $user_role) {
    return $user_role === $role || $user_role === 'admin';
}

function isActive($url, $current_url) {
    // Nettoyage des URLs pour comparaison
    $url = rtrim($url, '/');
    $current = rtrim(strtok($current_url, '?'), '/');

    if ($url === '' || $url === '/') {
        return $current === '' || $current === '/';
    }

    return strpos($current, $url) === 0;
}

// Configuration du menu selon le rôle
$menu_items = [];

// Menu pour tous les utilisateurs connectés
if ($user_role !== 'guest') {
    $menu_items['dashboard'] = [
        'label' => 'Tableau de Bord',
        'url' => '/',
        'icon' => 'dashboard',
        'permission' => null,
        'active' => isActive('/', $current_url)
    ];
}

// Menu Étudiant
if (hasRole('etudiant', $user_role)) {
    $menu_items['student'] = [
        'label' => 'Mon Espace Étudiant',
        'icon' => 'school',
        'children' => [
            [
                'label' => 'Mes Cours',
                'url' => '/student/courses',
                'icon' => 'book',
                'active' => isActive('/student/courses', $current_url)
            ],
            [
                'label' => 'Mes Notes',
                'url' => '/student/grades',
                'icon' => 'grade',
                'active' => isActive('/student/grades', $current_url)
            ],
            [
                'label' => 'Planning',
                'url' => '/student/schedule',
                'icon' => 'event',
                'active' => isActive('/student/schedule', $current_url)
            ],
            [
                'label' => 'Documents',
                'url' => '/student/documents',
                'icon' => 'description',
                'active' => isActive('/student/documents', $current_url)
            ]
        ]
    ];
}

// Menu Enseignant
if (hasRole('enseignant', $user_role)) {
    $menu_items['teacher'] = [
        'label' => 'Espace Enseignant',
        'icon' => 'person',
        'children' => [
            [
                'label' => 'Mes Cours',
                'url' => '/teacher/courses',
                'icon' => 'class',
                'active' => isActive('/teacher/courses', $current_url)
            ],
            [
                'label' => 'Évaluations',
                'url' => '/teacher/evaluations',
                'icon' => 'quiz',
                'active' => isActive('/teacher/evaluations', $current_url)
            ],
            [
                'label' => 'Étudiants',
                'url' => '/teacher/students',
                'icon' => 'groups',
                'active' => isActive('/teacher/students', $current_url)
            ],
            [
                'label' => 'Planning',
                'url' => '/teacher/schedule',
                'icon' => 'event',
                'active' => isActive('/teacher/schedule', $current_url)
            ]
        ]
    ];
}

// Menu Administration
if (hasRole('admin', $user_role) || hasPermission('admin_access', $user_permissions)) {
    $menu_items['admin'] = [
        'label' => 'Administration',
        'icon' => 'admin_panel_settings',
        'children' => [
            [
                'label' => 'Gestion Utilisateurs',
                'url' => '/admin/users',
                'icon' => 'people',
                'active' => isActive('/admin/users', $current_url),
                'permission' => 'manage_users'
            ],
            [
                'label' => 'Gestion Académique',
                'url' => '/admin/gestion-acad',
                'icon' => 'school',
                'active' => isActive('/admin/gestion-acad', $current_url),
                'permission' => 'manage_academic'
            ],
            [
                'label' => 'Cours & Programmes',
                'url' => '/admin/courses',
                'icon' => 'menu_book',
                'active' => isActive('/admin/courses', $current_url),
                'permission' => 'manage_courses'
            ],
            [
                'label' => 'Examens',
                'url' => '/admin/exams',
                'icon' => 'quiz',
                'active' => isActive('/admin/exams', $current_url),
                'permission' => 'manage_exams'
            ]
        ]
    ];

    $menu_items['system'] = [
        'label' => 'Système',
        'icon' => 'settings',
        'children' => [
            [
                'label' => 'Configuration',
                'url' => '/admin/config',
                'icon' => 'tune',
                'active' => isActive('/admin/config', $current_url),
                'permission' => 'system_config'
            ],
            [
                'label' => 'Permissions',
                'url' => '/admin/permissions',
                'icon' => 'security',
                'active' => isActive('/admin/permissions', $current_url),
                'permission' => 'manage_permissions'
            ],
            [
                'label' => 'Rapports',
                'url' => '/admin/reports',
                'icon' => 'assessment',
                'active' => isActive('/admin/reports', $current_url),
                'permission' => 'view_reports'
            ],
            [
                'label' => 'Logs Système',
                'url' => '/admin/logs',
                'icon' => 'bug_report',
                'active' => isActive('/admin/logs', $current_url),
                'permission' => 'view_logs'
            ]
        ]
    ];
}

// Menu Personnel/Scolarité
if (hasRole('personnel', $user_role) || hasPermission('staff_access', $user_permissions)) {
    $menu_items['staff'] = [
        'label' => 'Scolarité',
        'icon' => 'assignment',
        'children' => [
            [
                'label' => 'Inscriptions',
                'url' => '/staff/inscriptions',
                'icon' => 'how_to_reg',
                'active' => isActive('/staff/inscriptions', $current_url)
            ],
            [
                'label' => 'Documents',
                'url' => '/staff/documents',
                'icon' => 'description',
                'active' => isActive('/staff/documents', $current_url)
            ],
            [
                'label' => 'Planning',
                'url' => '/staff/planning',
                'icon' => 'event',
                'active' => isActive('/staff/planning', $current_url)
            ]
        ]
    ];
}

// Informations utilisateur pour la sidebar
$current_user = $current_user ?? $_SESSION['user_data'] ?? null;
$user_name = $current_user['nom'] ?? $current_user['name'] ?? 'Utilisateur';
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
?>

<aside class="gestionsoutenance-sidebar" id="sidebar">
    <div class="sidebar-content">

        <!-- Branding -->
        <div class="sidebar-brand">
            <div class="brand-logo">
                <span class="material-icons">school</span>
            </div>
            <span class="brand-text hide-when-collapsed">GestionMySoutenance</span>
        </div>

        <!-- Informations utilisateur -->
        <?php if ($user_role !== 'guest'): ?>
            <div class="admin-info hide-when-collapsed">
                <div class="admin-avatar" id="admin-avatar-initials">
                    <?= e($user_initials) ?>
                </div>
                <div class="admin-details">
                    <p class="admin-name" id="admin-name-display"><?= e($user_name) ?></p>
                    <p class="admin-role" id="admin-role-display"><?= e($user_role_display) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Navigation principale -->
        <nav class="sidebar-nav">
            <?php foreach ($menu_items as $section_key => $section): ?>

                <?php if (isset($section['children'])): ?>
                    <!-- Menu avec sous-éléments -->
                    <div class="collapsible-menu <?= $section_key ?>-menu" data-section="<?= $section_key ?>">
                        <div class="collapsible-header">
                            <div class="nav-item-content">
                                <span class="material-icons"><?= e($section['icon']) ?></span>
                                <span class="hide-when-collapsed"><?= e($section['label']) ?></span>
                            </div>
                            <span class="material-icons expand-icon hide-when-collapsed">chevron_right</span>
                        </div>

                        <div class="collapsible-content">
                            <?php foreach ($section['children'] as $item): ?>
                                <?php
                                // Vérifier les permissions pour cet élément
                                $can_access = true;
                                if (isset($item['permission'])) {
                                    $can_access = hasPermission($item['permission'], $user_permissions);
                                }
                                ?>

                                <?php if ($can_access): ?>
                                    <a href="<?= e($item['url']) ?>"
                                       class="nav-item <?= ($item['active'] ?? false) ? 'active' : '' ?>"
                                       data-tooltip="<?= e($item['label']) ?>">
                                        <span class="material-icons"><?= e($item['icon']) ?></span>
                                        <span class="hide-when-collapsed"><?= e($item['label']) ?></span>

                                        <?php if (isset($item['badge'])): ?>
                                            <span class="nav-badge <?= e($item['badge']['type'] ?? '') ?>">
                                            <?= e($item['badge']['count']) ?>
                                        </span>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Menu simple -->
                    <a href="<?= e($section['url']) ?>"
                       class="nav-item <?= ($section['active'] ?? false) ? 'active' : '' ?>"
                       data-tooltip="<?= e($section['label']) ?>">
                        <span class="material-icons"><?= e($section['icon']) ?></span>
                        <span class="hide-when-collapsed"><?= e($section['label']) ?></span>

                        <?php if (isset($section['badge'])): ?>
                            <span class="nav-badge <?= e($section['badge']['type'] ?? '') ?>">
                                <?= e($section['badge']['count']) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Footer de la sidebar -->
    <div class="sidebar-footer">

        <!-- Bouton de réduction -->
        <button class="sidebar-collapse-btn" onclick="toggleSidebarCollapse()" data-tooltip="Réduire le menu">
            <span class="material-icons">chevron_left</span>
        </button>

        <!-- Liens rapides (si non réduit) -->
        <div class="sidebar-quick-links hide-when-collapsed">
            <a href="/help" class="quick-link" data-tooltip="Aide">
                <span class="material-icons">help</span>
            </a>

            <?php if (hasRole('admin', $user_role)): ?>
                <a href="/admin/system-status" class="quick-link" data-tooltip="État du système">
                    <span class="material-icons">monitor_heart</span>
                </a>
            <?php endif; ?>

            <a href="/settings" class="quick-link" data-tooltip="Paramètres">
                <span class="material-icons">settings</span>
            </a>
        </div>

        <!-- Version de l'application -->
        <div class="app-version hide-when-collapsed">
            <small>v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?></small>
        </div>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initSidebarMenu();

        // Restaurer l'état de la sidebar
        const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
        }

        // Restaurer l'état des menus pliables
        restoreMenuStates();
    });

    function initSidebarMenu() {
        // Gestion des menus pliables
        document.querySelectorAll('.collapsible-header').forEach(header => {
            header.addEventListener('click', function() {
                const menu = this.closest('.collapsible-menu');
                const isOpen = menu.classList.contains('open');

                // Fermer tous les autres menus
                document.querySelectorAll('.collapsible-menu').forEach(m => {
                    if (m !== menu) {
                        m.classList.remove('open');
                    }
                });

                // Toggle le menu actuel
                menu.classList.toggle('open', !isOpen);

                // Sauvegarder l'état
                saveMenuState(menu.dataset.section, !isOpen);
            });
        });

        // Auto-ouvrir le menu contenant la page active
        const activeItem = document.querySelector('.collapsible-content .nav-item.active');
        if (activeItem) {
            const parentMenu = activeItem.closest('.collapsible-menu');
            if (parentMenu) {
                parentMenu.classList.add('open');
                saveMenuState(parentMenu.dataset.section, true);
            }
        }

        // Gestion des tooltips pour la sidebar réduite
        updateTooltips();
    }

    function toggleSidebarCollapse() {
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', isCollapsed);

        // Fermer tous les menus si on réduit
        if (isCollapsed) {
            document.querySelectorAll('.collapsible-menu').forEach(menu => {
                menu.classList.remove('open');
            });
        }

        // Mettre à jour les tooltips
        setTimeout(updateTooltips, 300);

        // Event personnalisé pour notifier le changement
        window.dispatchEvent(new CustomEvent('sidebar:toggle', {
            detail: { collapsed: isCollapsed }
        }));
    }

    function saveMenuState(section, isOpen) {
        const states = JSON.parse(localStorage.getItem('menu-states') || '{}');
        states[section] = isOpen;
        localStorage.setItem('menu-states', JSON.stringify(states));
    }

    function restoreMenuStates() {
        const states = JSON.parse(localStorage.getItem('menu-states') || '{}');

        Object.entries(states).forEach(([section, isOpen]) => {
            const menu = document.querySelector(`[data-section="${section}"]`);
            if (menu && isOpen) {
                menu.classList.add('open');
            }
        });
    }

    function updateTooltips() {
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');

        document.querySelectorAll('.nav-item, .quick-link').forEach(item => {
            if (isCollapsed) {
                const tooltip = item.getAttribute('data-tooltip');
                if (tooltip) {
                    item.title = tooltip;
                }
            } else {
                item.removeAttribute('title');
            }
        });
    }

    // Gestion responsive
    function handleResponsiveMenu() {
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            document.body.classList.remove('sidebar-collapsed');
        }
    }

    // Écouter les changements de taille d'écran
    window.addEventListener('resize', debounce(handleResponsiveMenu, 250));

    // Fonction utilitaire debounce
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Fermer la sidebar mobile en cliquant sur le contenu principal
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && !e.target.closest('.gestionsoutenance-sidebar') && !e.target.closest('.mobile-sidebar-toggle')) {
            document.body.classList.remove('sidebar-mobile-open');
        }
    });

    // API publique pour la sidebar
    window.SidebarAPI = {
        toggle: toggleSidebarCollapse,
        collapse: () => {
            document.body.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', 'true');
        },
        expand: () => {
            document.body.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', 'false');
        },
        openMenu: (section) => {
            const menu = document.querySelector(`[data-section="${section}"]`);
            if (menu) {
                menu.classList.add('open');
                saveMenuState(section, true);
            }
        },
        closeMenu: (section) => {
            const menu = document.querySelector(`[data-section="${section}"]`);
            if (menu) {
                menu.classList.remove('open');
                saveMenuState(section, false);
            }
        }
    };

    // Compatibilité avec l'ancien système
    window.toggleMobileSidebar = function() {
        document.body.classList.toggle('sidebar-mobile-open');
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    };
</script>

<style>
    /* Styles spécifiques au menu */
    .collapsible-menu {
        margin-bottom: var(--spacing-sm);
    }

    .collapsible-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-md) var(--spacing-lg);
        cursor: pointer;
        border-radius: var(--border-radius-lg);
        transition: all var(--transition-fast);
        color: var(--sidebar-text);
    }

    .collapsible-header:hover {
        background: var(--sidebar-hover-bg);
    }

    .nav-item-content {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
        flex: 1;
    }

    .expand-icon {
        transition: transform var(--transition-fast);
        font-size: 1.25rem;
    }

    .collapsible-menu.open .expand-icon {
        transform: rotate(90deg);
    }

    .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height var(--transition-normal);
        padding-left: var(--spacing-lg);
    }

    .collapsible-menu.open .collapsible-content {
        max-height: 500px;
    }

    .nav-badge {
        margin-left: auto;
        font-size: var(--font-size-xs);
        padding: 2px 6px;
        border-radius: var(--border-radius-full);
        background: var(--primary-accent);
        color: white;
        font-weight: var(--font-weight-medium);
        min-width: 18px;
        text-align: center;
    }

    .nav-badge.warning {
        background: var(--warning-accent);
    }

    .nav-badge.danger {
        background: var(--danger-accent);
    }

    .nav-badge.info {
        background: var(--info-accent);
    }

    .sidebar-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: var(--spacing-lg);
        margin-top: auto;
    }

    .sidebar-collapse-btn {
        width: 100%;
        padding: var(--spacing-md);
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: var(--border-radius-lg);
        color: var(--sidebar-text);
        cursor: pointer;
        transition: all var(--transition-fast);
        margin-bottom: var(--spacing-md);
    }

    .sidebar-collapse-btn:hover {
        background: var(--sidebar-hover-bg);
        border-color: var(--primary-accent);
    }

    .sidebar-collapsed .sidebar-collapse-btn .material-icons {
        transform: rotate(180deg);
    }

    .sidebar-quick-links {
        display: flex;
        gap: var(--spacing-sm);
        justify-content: center;
        margin-bottom: var(--spacing-md);
    }

    .quick-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: var(--border-radius-lg);
        color: var(--sidebar-text);
        text-decoration: none;
        transition: all var(--transition-fast);
    }

    .quick-link:hover {
        background: var(--sidebar-hover-bg);
        color: var(--primary-accent);
        text-decoration: none;
    }

    .app-version {
        text-align: center;
        color: var(--sidebar-text-muted);
        font-size: var(--font-size-xs);
    }

    /* États de la sidebar */
    .sidebar-collapsed .hide-when-collapsed {
        display: none;
    }

    .sidebar-collapsed .show-when-collapsed {
        display: block;
    }

    .sidebar-collapsed .gestionsoutenance-sidebar {
        width: 70px;
        padding: var(--spacing-lg) var(--spacing-sm);
    }

    .sidebar-collapsed .nav-item {
        justify-content: center;
        padding: var(--spacing-md);
    }

    .sidebar-collapsed .collapsible-header {
        justify-content: center;
        padding: var(--spacing-md);
    }

    .sidebar-collapsed .admin-info {
        text-align: center;
    }

    .sidebar-collapsed .admin-avatar {
        margin: 0 auto;
    }

    /* Mobile */
    @media (max-width: 768px) {
        .gestionsoutenance-sidebar {
            transform: translateX(-100%);
            z-index: 1050;
        }

        .sidebar-mobile-open .gestionsoutenance-sidebar,
        .gestionsoutenance-sidebar.open {
            transform: translateX(0);
        }

        .sidebar-mobile-open::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        .main-content-area {
            margin-left: 0 !important;
        }

        .no-sidebar {
            margin-left: 0;
        }
    }

    /* Animation d'entrée */
    .nav-item {
        animation: slideInLeft 0.3s ease-out;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>