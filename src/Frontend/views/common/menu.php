<?php
/**
 * Menu latéral modernisé - GestionMySoutenance
 * Navigation principale avec permissions et rôles
 *
 * Ce fichier charge les données de menu dynamiquement depuis la base de données.
 */

// Récupération des données utilisateur et permissions
// Assurez-vous que l'ID du groupe de l'utilisateur est accessible ici, par exemple via la session.
$user_role = $_SESSION['user_role'] ?? 'guest'; // Ceci peut être le libellé du rôle (ex: 'admin', 'etudiant')
$user_group_id = $_SESSION['user_group_id'] ?? null; // C'est l'ID du groupe ('ADMIN', 'ETUDIANT', etc.) depuis la BD
$current_url = $_SERVER['REQUEST_URI'];

// Fonction helper pour échapper les sorties HTML (sécurité)
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Fonction helper pour vérifier l'état actif de l'URL
function isActive($url, $current_url) {
    // Nettoyage des URLs pour comparaison
    $url = rtrim($url, '/');
    $current = rtrim(strtok($current_url, '?'), '/');

    if ($url === '' || $url === '/') {
        return $current === '' || $current === '/';
    }

    // Gère les cas où l'URL du menu est une base (ex: /admin/configuration) et l'URL actuelle est plus spécifique (ex: /admin/configuration/parametres)
    return strpos($current, $url) === 0;
}

// Initialisation du tableau des éléments de menu
$menu_items = [];

// --- DÉBUT DE LA LOGIQUE DE CHARGEMENT DU MENU DEPUIS LA BASE DE DONNÉES ---

if ($user_group_id) {
    $raw_db_menu_data = []; // Ce tableau contiendra les données brutes de la base de données

    try {
        // C'est LA PARTIE QUE VOUS DEVEZ ADAPTER À VOTRE SYSTÈME D'ACCÈS À LA BASE DE DONNÉES.
        // Si vous utilisez votre classe src/Config/Database.php et/ou vos Services (ex: ServiceSecurite):

        // Exemple si vous avez un conteneur d'injection de dépendances (comme src/Config/Container.php)
        // require_once __DIR__ . '/../../Config/Container.php';
        // $container = new \src\Config\Container();
        // $serviceSecurite = $container->get(\src\Backend\Service\Securite\ServiceSecurite::class);
        // $raw_db_menu_data = $serviceSecurite->getAccessibleTraitementsForGroup($user_group_id);
        // Assurez-vous que ServiceSecurite::getAccessibleTraitementsForGroup existe et exécute la requête SQL ci-dessous.

        // Si vous travaillez avec PDO directement (moins recommandé pour une grande application):
        // Assurez-vous que $pdo_connection est votre objet PDO global ou une connexion obtenue de votre classe Database.
        // global $pdo_connection; // OU $db = new \src\Config\Database(); $pdo_connection = $db->getConnection();
        // if ($pdo_connection) {
        //     $stmt = $pdo_connection->prepare("
        //         SELECT t.id_traitement, t.libelle_traitement, t.id_parent_traitement, t.icone_class, t.url_associee, t.ordre_affichage
        //         FROM traitement t
        //         JOIN groupe_traitement gt ON t.id_traitement = gt.id_traitement
        //         WHERE gt.id_groupe_utilisateur = :user_group_id
        //         ORDER BY t.ordre_affichage ASC
        //     ");
        //     $stmt->execute([':user_group_id' => $user_group_id]);
        //     $raw_db_menu_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // }

        // --- DONNÉES D'EXEMPLE POUR LE DÉVELOPPEMENT (À SUPPRIMER EN PRODUCTION) ---
        // Cette section simule ce que votre base de données devrait retourner.
        // REMPLACEZ CELA PAR LE VRAI APPEL À VOTRE BASE DE DONNÉES !
        $sample_db_data = [];
        if ($user_group_id === 'ADMIN') {
            $sample_db_data = [
                ['id_traitement' => 'MENU_DASHBOARDS', 'libelle_traitement' => 'Tableaux de Bord', 'id_parent_traitement' => null, 'icone_class' => 'fas fa-tachometer-alt', 'url_associee' => null, 'ordre_affichage' => 10],
                ['id_traitement' => 'TRAIT_ADMIN_DASHBOARD_ACCEDER', 'libelle_traitement' => 'Accéder Dashboard Admin', 'id_parent_traitement' => 'MENU_DASHBOARDS', 'icone_class' => 'fas fa-chart-line', 'url_associee' => '/../Administration/dashboard', 'ordre_affichage' => 11],
                ['id_traitement' => 'MENU_ADMINISTRATION', 'libelle_traitement' => 'Administration', 'id_parent_traitement' => null, 'icone_class' => 'fas fa-cogs', 'url_associee' => null, 'ordre_affichage' => 40],
                ['id_traitement' => 'MENU_GESTION_COMPTES', 'libelle_traitement' => 'Gestion des Comptes', 'id_parent_traitement' => 'MENU_ADMINISTRATION', 'icone_class' => 'fas fa-users', 'url_associee' => null, 'ordre_affichage' => 41],
                ['id_traitement' => 'TRAIT_ADMIN_GERER_UTILISATEURS_LISTER', 'libelle_traitement' => 'Lister Utilisateurs', 'id_parent_traitement' => 'MENU_GESTION_COMPTES', 'icone_class' => 'fas fa-list', 'url_associee' => '/../Administration/utilisateurs/liste', 'ordre_affichage' => 410],
                ['id_traitement' => 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER', 'libelle_traitement' => 'Créer Utilisateur', 'id_parent_traitement' => 'MENU_GESTION_COMPTES', 'icone_class' => 'fas fa-user-plus', 'url_associee' => '/../Administration/utilisateurs/creer', 'ordre_affichage' => 411],
                ['id_traitement' => 'TRAIT_ADMIN_CONFIG_ACCEDER', 'libelle_traitement' => 'Accéder Configuration', 'id_parent_traitement' => 'MENU_ADMINISTRATION', 'icone_class' => 'fas fa-sliders-h', 'url_associee' => '/../Administration/configuration', 'ordre_affichage' => 42],
                ['id_traitement' => 'TRAIT_ADMIN_CONFIG_ANNEES_GERER', 'libelle_traitement' => 'Gérer Années Académiques', 'id_parent_traitement' => 'TRAIT_ADMIN_CONFIG_ACCEDER', 'icone_class' => null, 'url_associee' => null, 'ordre_affichage' => 0],
                ['id_traitement' => 'TRAIT_ADMIN_ACCES_FICHIERS_PROTEGES', 'libelle_traitement' => 'Accéder Fichiers Protégés', 'id_parent_traitement' => null, 'icone_class' => null, 'url_associee' => null, 'ordre_affichage' => 0], // Exemple de traitement sans parent direct de type MENU
            ];
        } elseif ($user_group_id === 'ETUDIANT') {
            $sample_db_data = [
                ['id_traitement' => 'MENU_DASHBOARDS', 'libelle_traitement' => 'Tableaux de Bord', 'id_parent_traitement' => null, 'icone_class' => 'fas fa-tachometer-alt', 'url_associee' => null, 'ordre_affichage' => 10],
                ['id_traitement' => 'TRAIT_ETUDIANT_DASHBOARD_ACCEDER', 'libelle_traitement' => 'Accéder Dashboard Étudiant', 'id_parent_traitement' => 'MENU_DASHBOARDS', 'icone_class' => 'fas fa-user-graduate', 'url_associee' => '/../Etudiant/dashboard', 'ordre_affichage' => 12],
                ['id_traitement' => 'MENU_ETUDIANT', 'libelle_traitement' => 'Espace Étudiant', 'id_parent_traitement' => null, 'icone_class' => 'fas fa-user-graduate', 'url_associee' => null, 'ordre_affichage' => 20],
                ['id_traitement' => 'TRAIT_ETUDIANT_PROFIL_GERER', 'libelle_traitement' => 'Gérer Profil Étudiant', 'id_parent_traitement' => 'MENU_ETUDIANT', 'icone_class' => 'fas fa-user-circle', 'url_associee' => '/../Etudiant/profil', 'ordre_affichage' => 20],
                ['id_traitement' => 'MENU_RAPPORT_ETUDIANT', 'libelle_traitement' => 'Rapports Étudiant', 'id_parent_traitement' => 'MENU_ETUDIANT', 'icone_class' => 'fas fa-file-alt', 'url_associee' => null, 'ordre_affichage' => 21],
                ['id_traitement' => 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE', 'libelle_traitement' => 'Soumettre Rapport', 'id_parent_traitement' => 'MENU_RAPPORT_ETUDIANT', 'icone_class' => 'fas fa-upload', 'url_associee' => '/../Etudiant/rapport/redaction', 211],
                ['id_traitement' => 'TRAIT_ETUDIANT_RAPPORT_SUIVRE', 'libelle_traitement' => 'Suivre Rapport', 'id_parent_traitement' => 'MENU_RAPPORT_ETUDIANT', 'icone_class' => 'fas fa-eye', 'url_associee' => '/../Etudiant/rapport/suivi', 212],
            ];
        }
        // TODO: Ajoutez ici les données d'exemple pour 'COMMISSION', 'PERSONNEL', 'ENSEIGNANT'
        // en fonction de ce que vous attendez de la base de données.

        $raw_db_menu_data = $sample_db_data; // REMPLACEZ CETTE LIGNE PAR VOTRE VRAI APPEL À LA DB !

        // --- FIN DES DONNÉES D'EXEMPLE ---

    } catch (Throwable $e) { // Utilisez Throwable pour attraper toutes les erreurs
        error_log("Erreur de chargement du menu depuis la DB: " . $e->getMessage());
        $raw_db_menu_data = []; // Assurez-vous que c'est vide en cas d'erreur
    }

    // Étape 2: Construire la structure hiérarchique du menu à partir des données brutes
    $indexed_menu_items = [];
    foreach ($raw_db_menu_data as $item) {
        // Logique de mapping des icônes Font Awesome vers Material Icons
        $icon_name = $item['icone_class'];
        if ($icon_name && strpos($icon_name, 'fas fa-') === 0) {
            $icon_key = str_replace('fas fa-', '', $icon_name);
            $icon_mapping = [
                'tachometer-alt' => 'dashboard',
                'cogs' => 'settings',
                'gavel' => 'gavel',
                'user-graduate' => 'school', // Ou 'person_pin'
                'users' => 'people',
                'user-tie' => 'person',
                'file-alt' => 'description',
                'chart-line' => 'bar_chart',
                'user-plus' => 'person_add',
                'list' => 'list',
                'clipboard-list' => 'assignment',
                'user-circle' => 'account_circle',
                'upload' => 'upload',
                'eye' => 'visibility',
                'clipboard-check' => 'check_circle',
                'question-circle' => 'help',
                'graduation-cap' => 'school',
                'sliders-h' => 'tune',
            ];
            $icon_name = $icon_mapping[$icon_key] ?? $icon_key; // Utilise le nom Material Icons ou la clé brute si non trouvé
        } else {
            $icon_name = $icon_name ?: 'circle'; // Icône par défaut si non spécifiée ou mapping impossible
        }

        $indexed_menu_items[$item['id_traitement']] = [
            'label' => $item['libelle_traitement'],
            'url' => $item['url_associee'], // Peut être NULL
            'icon' => $icon_name,
            'id_traitement' => $item['id_traitement'], // Garder l'ID pour référence interne
            'active' => isActive($item['url_associee'] ?? '', $current_url), // Gérer les URLs NULL
            'id_parent_traitement' => $item['id_parent_traitement'],
            'ordre_affichage' => $item['ordre_affichage'],
            'children' => []
        ];
    }

    $final_menu_tree = [];
    foreach ($indexed_menu_items as $id => $item) {
        if ($item['id_parent_traitement'] === null) {
            // C'est un élément de menu de premier niveau
            $final_menu_tree[$id] = $item;
        } else {
            // C'est un sous-élément, rattachez-le à son parent
            if (isset($indexed_menu_items[$item['id_parent_traitement']])) {
                // Ajouter l'enfant au parent, en conservant l'ID de l'enfant comme clé
                $indexed_menu_items[$item['id_parent_traitement']]['children'][$id] = $item;
            }
            // Si le parent n'est pas dans les traitements accessibles à l'utilisateur,
            // cet enfant ne sera pas inclus dans le menu final affiché.
        }
    }

    // Triez les éléments de premier niveau par ordre_affichage
    uasort($final_menu_tree, function($a, $b) {
        return $a['ordre_affichage'] <=> $b['ordre_affichage'];
    });

    // Triez les enfants de chaque élément de menu
    foreach ($final_menu_tree as $id => &$parent) { // Utilisez & pour modifier le tableau original
        if (!empty($parent['children'])) {
            uasort($parent['children'], function($a, $b) {
                return $a['ordre_affichage'] <=> $b['ordre_affichage'];
            });
        }
    }
    unset($parent); // Supprime la référence après la boucle pour éviter des effets secondaires

    $menu_items = $final_menu_tree; // Le tableau final $menu_items est maintenant rempli par la DB

    // Vous pouvez toujours ajouter des éléments statiques pour tous les utilisateurs si nécessaire,
    // qui ne sont pas gérés par la table `traitement` (ex: Déconnexion, Accueil si non géré par DB).
    // Exemple pour un Dashboard principal qui serait toujours présent indépendamment de la BD.
    if ($user_role !== 'guest' && !isset($menu_items['MENU_DASHBOARDS'])) {
        $menu_items = ['HOME_DASHBOARD_COMMON' => [ // Utilisez un ID unique pour éviter les conflits
                'label' => 'Tableau de Bord',
                'url' => '/',
                'icon' => 'dashboard',
                'id_traitement' => 'HOME_DASHBOARD_COMMON',
                'id_parent_traitement' => null,
                'ordre_affichage' => 5, // Un ordre d'affichage très bas pour qu'il soit en haut
                'active' => isActive('/', $current_url),
                'children' => []
            ]] + $menu_items; // Ajoute en tête du tableau
        // Re-trier après ajout si l'ordre est important par rapport aux éléments existants.
        uasort($menu_items, function($a, $b) {
            return $a['ordre_affichage'] <=> $b['ordre_affichage'];
        });
    }

} else {
    // Si l'utilisateur n'est pas connecté ou n'a pas de groupe
    $menu_items = []; // Pas de menu pour les invités, ou seulement un menu "login"
}

// --- FIN DE LA LOGIQUE DE CHARGEMENT DU MENU DEPUIS LA BASE DE DONNÉES ---

// Les variables d'information utilisateur doivent être définies AVANT le bloc HTML
$current_user = $_SESSION['user_data'] ?? null; // Assurez-vous que vos données utilisateur sont ici
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
    'ADMIN' => 'Administrateur',
    'ETUDIANT' => 'Étudiant',
    'ENSEIGNANT' => 'Enseignant',
    'COMMISSION' => 'Commission',
    'PERSONNEL' => 'Personnel Administratif'
];
// Utilisez l'ID du groupe pour l'affichage précis du rôle
$user_role_display = $role_display[$user_group_id] ?? ucfirst($user_role);

error_log("DEBUG Menu: Contenu final du menu pour l'utilisateur: " . json_encode($menu_items));

?>

<aside class="gestionsoutenance-sidebar" id="sidebar">
    <div class="sidebar-content">

        <div class="sidebar-brand">
            <div class="brand-logo">
                <span class="material-icons">school</span>
            </div>
            <span class="brand-text hide-when-collapsed">GestionMySoutenance</span>
        </div>

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

        <nav class="sidebar-nav">
            <?php foreach ($menu_items as $section_key => $section): ?>

                <?php
                // Si l'élément de premier niveau n'a pas d'URL mais a des enfants, il est un menu déroulant
                $is_collapsible_parent = empty($section['url']) && !empty($section['children']);
                ?>

                <?php if ($is_collapsible_parent): ?>
                    <div class="collapsible-menu <?= e($section['id_traitement']) ?>-menu" data-section="<?= e($section['id_traitement']) ?>">
                        <div class="collapsible-header">
                            <div class="nav-item-content">
                                <span class="material-icons"><?= e($section['icon']) ?></span>
                                <span class="hide-when-collapsed"><?= e($section['label']) ?></span>
                            </div>
                            <span class="material-icons expand-icon hide-when-collapsed">chevron_right</span>
                        </div>

                        <div class="collapsible-content">
                            <?php foreach ($section['children'] as $item_child): ?>
                                <?php
                                // Vérifiez si l'enfant a une URL pour être affichable en tant que lien
                                // Les traitements sans URL sont considérés comme de pures permissions ou sous-actions
                                // et ne seront pas affichés comme des liens de menu ici.
                                if (empty($item_child['url'])) {
                                    continue; // Passez si l'élément enfant n'a pas d'URL navigable
                                }
                                ?>
                                <a href="<?= e($item_child['url']) ?>"
                                   class="nav-item <?= ($item_child['active'] ?? false) ? 'active' : '' ?>"
                                   data-tooltip="<?= e($item_child['label']) ?>">
                                    <span class="material-icons"><?= e($item_child['icon']) ?></span>
                                    <span class="hide-when-collapsed"><?= e($item_child['label']) ?></span>

                                    <?php if (isset($item_child['badge'])): ?>
                                        <span class="nav-badge <?= e($item_child['badge']['type'] ?? '') ?>">
                                        <?= e($item_child['badge']['count']) ?>
                                    </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php else: ?>
                    <?php
                    // Assurez-vous que l'élément a une URL pour être affiché
                    if (empty($section['url'])) {
                        continue; // Passez si l'élément de premier niveau n'a pas d'URL navigable
                    }
                    ?>
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

    <div class="sidebar-footer">

        <button class="sidebar-collapse-btn" onclick="toggleSidebarCollapse()" data-tooltip="Réduire le menu">
            <span class="material-icons">chevron_left</span>
        </button>

        <div class="sidebar-quick-links hide-when-collapsed">
            <a href="/help" class="quick-link" data-tooltip="Aide">
                <span class="material-icons">help</span>
            </a>

            <?php if ($user_group_id === 'ADMIN'): ?>
                <a href="/admin/system-status" class="quick-link" data-tooltip="État du système">
                    <span class="material-icons">monitor_heart</span>
                </a>
            <?php endif; ?>

            <a href="/settings" class="quick-link" data-tooltip="Paramètres">
                <span class="material-icons">settings</span>
            </a>
        </div>

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
    /* Styles spécifiques au menu (inchangés) */
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