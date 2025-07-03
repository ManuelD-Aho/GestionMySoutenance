<?php
/**
 * Header modernisé - GestionMySoutenance
 * Header principal avec navigation, notifications et profil utilisateur
 */

// Récupération des données utilisateur
$current_user = $current_user ?? $_SESSION['user_data'] ?? null;
$user_name = $current_user['nom'] ?? $current_user['name'] ?? 'Utilisateur';
$user_email = $current_user['email'] ?? '';
$user_role = $current_user['role'] ?? $_SESSION['user_role'] ?? 'Utilisateur';
$user_avatar = $current_user['avatar'] ?? '';

// Génération des initiales pour l'avatar
$user_initials = '';
if (!empty($user_name)) {
    $name_parts = explode(' ', trim($user_name));
    $user_initials = strtoupper(substr($name_parts[0], 0, 1));
    if (isset($name_parts[1])) {
        $user_initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
}

// Données des notifications
$notifications_count = $notifications_count ?? $_SESSION['notifications_count'] ?? 0;
$notifications = $_SESSION['notifications'] ?? [];

// Rôle formaté pour l'affichage
$role_display = [
    'admin' => 'Administrateur',
    'enseignant' => 'Enseignant',
    'etudiant' => 'Étudiant',
    'personnel' => 'Personnel',
    'guest' => 'Invité'
];
$user_role_display = $role_display[$user_role] ?? ucfirst($user_role);

// Déterminer si on affiche le header simplifié ou complet
$is_authenticated = isset($_SESSION['user_id']);
$show_search = $is_admin_page ?? false;
?>

<header class="main-header" id="main-header">
    <div class="header-container">

        <!-- Partie gauche du header -->
        <div class="header-left">
            <!-- Toggle sidebar (desktop) -->
            <button class="sidebar-toggle d-none d-md-flex" onclick="toggleSidebar()" data-tooltip="Réduire/Étendre le menu">
                <span class="material-icons">menu</span>
            </button>

            <!-- Titre de la page -->
            <div class="header-title-section">
                <h1 class="header-title" id="page-title">
                    <?= e($pageTitle ?? 'GestionMySoutenance') ?>
                </h1>

                <!-- Badge du module actif (si applicable) -->
                <?php if (isset($module_badge)): ?>
                    <span class="module-badge admin-badge <?= e($module_badge['type'] ?? 'info') ?>">
                        <?php if (isset($module_badge['icon'])): ?>
                            <span class="material-icons"><?= e($module_badge['icon']) ?></span>
                        <?php endif; ?>
                        <?= e($module_badge['label']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Partie droite du header -->
        <div class="header-right">

            <!-- Recherche globale (si activée) -->
            <?php if ($show_search): ?>
                <div class="global-search">
                    <span class="search-icon material-icons">search</span>
                    <input type="text"
                           class="search-input"
                           placeholder="Rechercher utilisateurs, cours..."
                           id="global-search-input"
                           autocomplete="off">

                    <!-- Résultats de recherche -->
                    <div class="search-results" id="search-results">
                        <!-- Les résultats seront chargés dynamiquement -->
                    </div>
                </div>
            <?php endif; ?>

            <!-- Notifications -->
            <?php if ($is_authenticated): ?>
                <div class="notifications-dropdown">
                    <button class="notifications-trigger"
                            onclick="toggleNotifications()"
                            data-tooltip="Notifications"
                            aria-label="Notifications">
                        <span class="material-icons">notifications</span>
                        <?php if ($notifications_count > 0): ?>
                            <span class="notifications-badge"><?= min($notifications_count, 99) ?></span>
                        <?php endif; ?>
                    </button>

                    <div class="notifications-dropdown-content" id="notifications-dropdown">
                        <div class="notifications-header">
                            <h3>Notifications</h3>
                            <?php if ($notifications_count > 0): ?>
                                <button class="btn-link" onclick="markAllAsRead()">
                                    Tout marquer comme lu
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="notifications-list">
                            <?php if (empty($notifications)): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <span class="material-icons">notifications_none</span>
                                    </div>
                                    <p class="empty-state-title">Aucune notification</p>
                                    <p class="empty-state-description">Vous êtes à jour !</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_slice($notifications, 0, 10) as $notification): ?>
                                    <div class="notification-item <?= $notification['read'] ? '' : 'unread' ?>">
                                        <div class="notification-icon <?= e($notification['type'] ?? 'info') ?>">
                                            <span class="material-icons"><?= e($notification['icon'] ?? 'info') ?></span>
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-title"><?= e($notification['title']) ?></div>
                                            <div class="notification-text"><?= e($notification['message']) ?></div>
                                            <div class="notification-time">
                                                <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if (count($notifications) > 0): ?>
                            <div class="notifications-footer">
                                <a href="/notifications">Voir toutes les notifications</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Toggle thème (optionnel) -->
            <button class="theme-toggle d-none"
                    onclick="toggleTheme()"
                    data-tooltip="Changer le thème"
                    aria-label="Changer le thème">
                <span class="material-icons">dark_mode</span>
            </button>

            <!-- Raccourcis rapides (admin seulement) -->
            <?php if ($is_admin_page): ?>
                <div class="quick-actions d-none d-lg-flex">
                    <button class="btn btn-ghost btn-icon"
                            onclick="openQuickAdd()"
                            data-tooltip="Ajouter rapidement">
                        <span class="material-icons">add_circle_outline</span>
                    </button>

                    <button class="btn btn-ghost btn-icon"
                            onclick="openSystemStatus()"
                            data-tooltip="État du système">
                        <span class="material-icons">monitor_heart</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Profil utilisateur -->
            <?php if ($is_authenticated): ?>
                <div class="user-profile" onclick="toggleUserMenu()">
                    <div class="user-avatar">
                        <?php if (!empty($user_avatar)): ?>
                            <img src="<?= e($user_avatar) ?>" alt="Avatar" loading="lazy">
                        <?php else: ?>
                            <?= e($user_initials) ?>
                        <?php endif; ?>
                    </div>

                    <div class="user-info d-none d-md-block">
                        <div class="user-name"><?= e($user_name) ?></div>
                        <div class="user-role"><?= e($user_role_display) ?></div>
                    </div>

                    <span class="material-icons d-none d-md-inline">expand_more</span>

                    <!-- Menu déroulant utilisateur -->
                    <div class="dropdown-menu user-menu" id="user-menu">
                        <div class="dropdown-header">
                            <div class="user-avatar-large">
                                <?php if (!empty($user_avatar)): ?>
                                    <img src="<?= e($user_avatar) ?>" alt="Avatar" loading="lazy">
                                <?php else: ?>
                                    <?= e($user_initials) ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name-large"><?= e($user_name) ?></div>
                                <div class="user-email"><?= e($user_email) ?></div>
                                <div class="user-role-badge">
                                <span class="admin-badge <?= $user_role === 'admin' ? 'danger' : 'info' ?>">
                                    <?= e($user_role_display) ?>
                                </span>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="/profile" class="dropdown-item">
                            <span class="material-icons">person</span>
                            Mon Profil
                        </a>

                        <a href="/settings" class="dropdown-item">
                            <span class="material-icons">settings</span>
                            Paramètres
                        </a>

                        <?php if ($user_role === 'admin'): ?>
                            <div class="dropdown-divider"></div>
                            <a href="/admin" class="dropdown-item">
                                <span class="material-icons">admin_panel_settings</span>
                                Administration
                            </a>
                        <?php endif; ?>

                        <div class="dropdown-divider"></div>

                        <a href="/help" class="dropdown-item">
                            <span class="material-icons">help</span>
                            Aide
                        </a>

                        <button class="dropdown-item" onclick="logout()">
                            <span class="material-icons">logout</span>
                            Se déconnecter
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Boutons de connexion si non connecté -->
                <div class="auth-buttons">
                    <a href="/login" class="btn btn-outline">Connexion</a>
                    <a href="/register" class="btn btn-primary">Inscription</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barre de progression pour les actions en cours (optionnelle) -->
    <div class="progress-container d-none" id="header-progress">
        <div class="progress-bar" id="header-progress-bar"></div>
    </div>
</header>

<!-- Quick Add Modal (admin) -->
<?php if ($is_admin_page): ?>
    <div class="modal-overlay" id="quick-add-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <span class="material-icons">add_circle</span>
                    Ajout Rapide
                </h2>
                <button class="modal-close" onclick="closeQuickAdd()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="quick-add-grid">
                    <button class="quick-add-option" onclick="addUser()">
                        <span class="material-icons">person_add</span>
                        <span>Utilisateur</span>
                    </button>
                    <button class="quick-add-option" onclick="addCourse()">
                        <span class="material-icons">add_circle</span>
                        <span>Cours</span>
                    </button>
                    <button class="quick-add-option" onclick="addExam()">
                        <span class="material-icons">quiz</span>
                        <span>Examen</span>
                    </button>
                    <button class="quick-add-option" onclick="addAnnouncement()">
                        <span class="material-icons">campaign</span>
                        <span>Annonce</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Fonctions JavaScript pour le header
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        let notificationsOpen = false;
        let userMenuOpen = false;
        let searchTimeout = null;

        // Fermer les dropdowns en cliquant ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.notifications-dropdown')) {
                closeNotifications();
            }
            if (!e.target.closest('.user-profile')) {
                closeUserMenu();
            }
            if (!e.target.closest('.global-search')) {
                closeSearchResults();
            }
        });

        // Recherche globale
        const searchInput = document.getElementById('global-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();

                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => performGlobalSearch(query), 300);
                } else {
                    closeSearchResults();
                }
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.blur();
                    closeSearchResults();
                }
            });
        }
    });

    // Gestion de la sidebar
    function toggleSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed',
            document.body.classList.contains('sidebar-collapsed'));
    }

    // Gestion des notifications
    function toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        notificationsOpen = !notificationsOpen;

        if (notificationsOpen) {
            dropdown.classList.add('show');
            loadNotifications();
        } else {
            dropdown.classList.remove('show');
        }
    }

    function closeNotifications() {
        document.getElementById('notifications-dropdown')?.classList.remove('show');
        notificationsOpen = false;
    }

    function markAllAsRead() {
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.AppConfig?.csrfToken || ''
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('unread');
                    });
                    document.querySelector('.notifications-badge')?.remove();
                    window.GestionMySoutenance?.showFlashMessage('success', 'Notifications marquées comme lues');
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    function loadNotifications() {
        // Charger les notifications via AJAX si nécessaire
        // Cette fonction peut être étendue pour charger dynamiquement
    }

    // Gestion du menu utilisateur
    function toggleUserMenu() {
        const userMenu = document.getElementById('user-menu');
        userMenuOpen = !userMenuOpen;

        if (userMenuOpen) {
            userMenu.classList.add('show');
        } else {
            userMenu.classList.remove('show');
        }
    }

    function closeUserMenu() {
        document.getElementById('user-menu')?.classList.remove('show');
        userMenuOpen = false;
    }

    // Déconnexion
    function logout() {
        if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
            window.location.href = '/logout';
        }
    }

    // Recherche globale
    function performGlobalSearch(query) {
        const resultsContainer = document.getElementById('search-results');
        if (!resultsContainer) return;

        resultsContainer.innerHTML = '<div class="search-loading">Recherche...</div>';
        resultsContainer.classList.add('show');

        fetch(`/api/search?q=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-Token': window.AppConfig?.csrfToken || ''
            }
        })
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data.results || []);
            })
            .catch(error => {
                console.error('Erreur de recherche:', error);
                resultsContainer.innerHTML = '<div class="search-error">Erreur de recherche</div>';
            });
    }

    function displaySearchResults(results) {
        const container = document.getElementById('search-results');

        if (results.length === 0) {
            container.innerHTML = '<div class="search-no-results">Aucun résultat trouvé</div>';
            return;
        }

        const html = results.map(result => `
        <div class="search-result-item" onclick="location.href='${result.url}'">
            <div class="search-result-icon">
                <span class="material-icons">${result.icon || 'search'}</span>
            </div>
            <div class="search-result-content">
                <div class="search-result-title">${result.title}</div>
                <div class="search-result-description">${result.description || ''}</div>
            </div>
        </div>
    `).join('');

        container.innerHTML = html;
    }

    function closeSearchResults() {
        document.getElementById('search-results')?.classList.remove('show');
    }

    // Quick Add Modal (admin)
    function openQuickAdd() {
        document.getElementById('quick-add-modal')?.classList.add('active');
    }

    function closeQuickAdd() {
        document.getElementById('quick-add-modal')?.classList.remove('active');
    }

    // Actions rapides
    function addUser() {
        closeQuickAdd();
        window.location.href = '/admin/users/create';
    }

    function addCourse() {
        closeQuickAdd();
        window.location.href = '/admin/courses/create';
    }

    function addExam() {
        closeQuickAdd();
        window.location.href = '/admin/exams/create';
    }

    function addAnnouncement() {
        closeQuickAdd();
        window.location.href = '/admin/announcements/create';
    }

    function openSystemStatus() {
        window.GestionMySoutenance?.showFlashMessage('info', 'Fonctionnalité en développement');
    }

    // Toggle thème
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        const icon = document.querySelector('.theme-toggle .material-icons');
        if (icon) {
            icon.textContent = newTheme === 'dark' ? 'light_mode' : 'dark_mode';
        }
    }

    // Mise à jour du titre de la page
    function updatePageTitle(newTitle) {
        const titleElement = document.getElementById('page-title');
        if (titleElement) {
            titleElement.textContent = newTitle;
        }
        document.title = newTitle + ' - GestionMySoutenance';
    }

    // API publique
    window.HeaderAPI = {
        updatePageTitle,
        toggleNotifications,
        toggleUserMenu,
        performGlobalSearch,
        markAllAsRead
    };
</script>

<style>
    /* Styles spécifiques au header */
    .quick-add-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-lg);
        margin-top: var(--spacing-md);
    }

    .quick-add-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-xl);
        background: var(--hover-bg);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-lg);
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
        color: var(--text-primary);
    }

    .quick-add-option:hover {
        background: var(--primary-accent-light);
        border-color: var(--primary-accent);
        color: var(--primary-accent-dark);
        transform: translateY(-2px);
    }

    .quick-add-option .material-icons {
        font-size: 2rem;
        color: var(--primary-accent);
    }

    .search-loading,
    .search-error,
    .search-no-results {
        padding: var(--spacing-lg);
        text-align: center;
        color: var(--text-muted);
        font-size: var(--font-size-sm);
    }

    .dropdown-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .user-avatar-large {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius-full);
        background: var(--primary-accent);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: var(--font-weight-semibold);
        flex-shrink: 0;
    }

    .user-details {
        flex: 1;
        min-width: 0;
    }

    .user-name-large {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .user-email {
        font-size: var(--font-size-xs);
        color: var(--text-muted);
        margin-bottom: var(--spacing-xs);
    }

    .user-role-badge {
        display: flex;
    }

    @media (max-width: 768px) {
        .header-container {
            padding: var(--spacing-md);
        }

        .global-search {
            display: none;
        }

        .quick-actions {
            display: none !important;
        }

        .quick-add-grid {
            grid-template-columns: 1fr;
        }
    }
</style>