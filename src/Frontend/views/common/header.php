
<?php
// Retrieve user data and notification count from session
$currentUser = $_SESSION['user_data'] ?? null;
$userName = 'Utilisateur';
$userRole = 'Rôle';
$userEmail = 'email@example.com';
$userAvatar = null;
$notificationCount = $_SESSION['notification_count'] ?? 0;

if ($currentUser) {
    // The fields 'prenom', 'nom', 'email_principal' and 'photo_profil' are supposed to be in $currentUser
    $userName = htmlspecialchars($currentUser['prenom'] ?? '') . ' ' . htmlspecialchars($currentUser['nom'] ?? '');
    $userEmail = htmlspecialchars($currentUser['email_principal'] ?? 'email@example.com');
    // CORRECTED LINE 14: Ensure a string is passed to htmlspecialchars
    $userAvatar = htmlspecialchars($currentUser['photo_profil'] ?? ''); // Changed null to an empty string
    $userRole = htmlspecialchars($_SESSION['user_role_label'] ?? ($currentUser['id_type_utilisateur'] ?? 'Rôle Inconnu'));
}

$logoutUrl = '/logout'; // The logout URL
?>

<header class="dashboard-header">
    <div class="dashboard-header-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="header-title">
            <h1 class="page-title" id="pageTitle">Dashboard</h1>
        </div>
    </div>

    <div class="dashboard-header-center">
        <div class="search-container">
            <div class="search-bar" id="searchBar">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       placeholder="Rechercher..."
                       class="search-input"
                       id="searchInput"
                       autocomplete="off">
                <button class="search-clear" id="searchClear" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="search-suggestions" id="searchSuggestions" style="display: none;">
            </div>
        </div>
    </div>

    <div class="dashboard-header-right">
        <div class="header-actions">
            <button class="action-btn" id="fullscreenBtn" aria-label="Plein écran" title="Plein écran">
                <i class="fas fa-expand"></i>
            </button>

            <div class="notification-wrapper">
                <button class="action-btn notification-btn" id="notificationBtn" aria-label="Notifications" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="notification-badge" id="header-notification-count"><?php echo $notificationCount > 99 ? '99+' : $notificationCount; ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <button class="mark-all-read" id="markAllReadBtn">Tout marquer comme lu</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="no-notifications">Aucune notification pour l'instant.</div>
                    </div>
                    <div class="notification-footer">
                        <a href="/dashboard/notifications">Voir toutes les notifications</a>
                    </div>
                </div>
            </div>

            <button class="action-btn" id="settingsBtn" aria-label="Paramètres" title="Paramètres">
                <i class="fas fa-cog"></i>
            </button>
        </div>

        <div class="user-profile" id="userProfile">
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
            <button class="user-dropdown-toggle" id="userDropdownToggle" aria-label="Menu utilisateur">
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="user-dropdown" id="userDropdown">
                <div class="dropdown-header">
                    <div class="user-avatar-large">
                        <?php if ($userAvatar): ?>
                            <img src="<?php echo $userAvatar; ?>" alt="Avatar">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($userName, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $userName; ?></div>
                        <div class="user-email"><?php echo $userEmail; ?></div>
                    </div>
                </div>
                <div class="dropdown-body">
                    <a href="/dashboard/profile" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </a>
                    <a href="/dashboard/settings" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                    <a href="/dashboard/help" class="dropdown-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Aide</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo $logoutUrl; ?>" class="dropdown-item logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    /* Variables CSS pour une meilleure cohérence */
    :root {
        --header-bg: #ffffff;
        --header-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        --primary-color: #4f46e5;
        --primary-hover: #4338ca;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --text-muted: #9ca3af;
        --border-color: #e5e7eb;
        --hover-bg: #f3f4f6;
        --danger-color: #ef4444;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --border-radius: 8px;
        --border-radius-lg: 12px;
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        background: var(--header-bg);
        border-bottom: 1px solid var(--border-color);
        box-shadow: var(--header-shadow);
        position: sticky;
        top: 0;
        z-index: 1000;
        backdrop-filter: blur(8px);
        background: rgba(255, 255, 255, 0.95);
    }

    /* Section gauche */
    .dashboard-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .menu-toggle {
        background: none;
        border: none;
        font-size: 18px;
        color: var(--text-primary);
        cursor: pointer;
        padding: 8px;
        border-radius: var(--border-radius);
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .menu-toggle:hover {
        background-color: var(--hover-bg);
        transform: translateY(-1px);
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        white-space: nowrap;
    }

    /* Section centre - Recherche */
    .dashboard-header-center {
        flex: 1;
        display: flex;
        justify-content: center;
        max-width: 500px;
        margin: 0 24px;
    }

    .search-container {
        position: relative;
        width: 100%;
    }

    .search-bar {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 2px solid transparent;
        border-radius: 24px;
        padding: 10px 16px;
        transition: var(--transition);
        width: 100%;
    }

    .search-bar:focus-within {
        background: var(--header-bg);
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .search-icon {
        color: var(--text-muted);
        margin-right: 12px;
        font-size: 16px;
    }

    .search-input {
        border: none;
        background: none;
        outline: none;
        font-size: 14px;
        color: var(--text-primary);
        flex: 1;
        min-width: 0;
    }

    .search-input::placeholder {
        color: var(--text-muted);
    }

    .search-clear {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 50%;
        transition: var(--transition);
    }

    .search-clear:hover {
        background: var(--hover-bg);
    }

    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--header-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--header-shadow);
        margin-top: 4px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1001;
    }

    /* Section droite */
    .dashboard-header-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-right: 16px;
    }

    .action-btn {
        background: none;
        border: none;
        font-size: 18px;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 10px;
        border-radius: var(--border-radius);
        transition: var(--transition);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-btn:hover {
        background: var(--hover-bg);
        color: var(--text-primary);
        transform: translateY(-1px);
    }

    /* Notifications */
    .notification-wrapper {
        position: relative;
    }

    .notification-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        background: var(--danger-color);
        color: white;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 10px;
        min-width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--header-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--header-shadow);
        width: 320px;
        margin-top: 8px;
        z-index: 1001;
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none;
        transition: var(--transition);
    }

    .notification-dropdown.show {
        opacity: 1;
        transform: translateY(0);
        pointer-events: all;
    }

    .notification-header {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .mark-all-read {
        background: none;
        border: none;
        color: var(--primary-color);
        font-size: 12px;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: var(--transition);
    }

    .mark-all-read:hover {
        background: rgba(79, 70, 229, 0.1);
    }

    .notification-item {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        gap: 12px;
        cursor: pointer;
        transition: var(--transition);
    }

    .notification-item:hover {
        background: var(--hover-bg);
    }

    .notification-item.unread {
        background: rgba(79, 70, 229, 0.02);
        border-left: 3px solid var(--primary-color);
    }

    .notification-icon {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--hover-bg);
    }

    .text-blue { color: var(--primary-color); }
    .text-green { color: var(--success-color); }

    .notification-content p {
        margin: 0 0 4px 0;
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .notification-time {
        font-size: 12px;
        color: var(--text-muted);
    }

    .notification-footer {
        padding: 12px 16px;
        text-align: center;
        border-top: 1px solid var(--border-color);
    }

    .notification-footer a {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }

    /* Profil utilisateur */
    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 6px 12px 6px 6px;
        border-radius: 24px;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
        border: 1px solid transparent;
    }

    .user-profile:hover {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        border-color: var(--border-color);
        transform: translateY(-1px);
    }

    .user-avatar {
        position: relative;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--header-bg);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .status-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid var(--header-bg);
    }

    .status-indicator.online {
        background: var(--success-color);
    }

    .user-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .user-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 12px;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-dropdown-toggle {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: var(--transition);
    }

    .user-dropdown-toggle:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    /* Menu déroulant utilisateur */
    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--header-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--header-shadow);
        width: 280px;
        margin-top: 8px;
        z-index: 1001;
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none;
        transition: var(--transition);
    }

    .user-dropdown.show {
        opacity: 1;
        transform: translateY(0);
        pointer-events: all;
    }

    .dropdown-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .user-avatar-large {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--border-color);
    }

    .user-avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-avatar-large .avatar-placeholder {
        font-size: 18px;
    }

    .user-details .user-name {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .user-email {
        font-size: 12px;
        color: var(--text-muted);
    }

    .dropdown-body {
        padding: 8px 0;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition);
        font-size: 14px;
    }

    .dropdown-item:hover {
        background: var(--hover-bg);
    }

    .dropdown-item.logout {
        color: var(--danger-color);
    }

    .dropdown-item.logout:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    .dropdown-divider {
        height: 1px;
        background: var(--border-color);
        margin: 8px 0;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .dashboard-header {
            padding: 12px 16px;
        }

        .dashboard-header-center {
            margin: 0 16px;
        }

        .user-info {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-wrap: wrap;
            gap: 12px;
        }

        .dashboard-header-left {
            order: 1;
            flex: 1;
        }

        .dashboard-header-right {
            order: 2;
            flex-shrink: 0;
        }

        .dashboard-header-center {
            order: 3;
            flex-basis: 100%;
            margin: 0;
        }

        .page-title {
            font-size: 20px;
        }

        .header-actions {
            margin-right: 8px;
        }

        .action-btn {
            padding: 8px;
        }

        .notification-dropdown,
        .user-dropdown {
            width: 100vw;
            left: 0;
            right: 0;
            margin-top: 8px;
            border-radius: 0;
            max-width: none;
        }
    }

    @media (max-width: 480px) {
        .search-bar {
            padding: 8px 12px;
        }

        .user-profile {
            padding: 4px 8px 4px 4px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
        }
    }

    /* Animation pour les éléments qui apparaissent */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-dropdown.show,
    .user-dropdown.show {
        animation: fadeInUp 0.2s ease-out;
    }

    /* Focus states pour l'accessibilité */
    .menu-toggle:focus,
    .action-btn:focus,
    .user-dropdown-toggle:focus {
        outline: 2px solid var(--primary-color);
        outline-offset: 2px;
    }

    .search-input:focus {
        outline: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments du DOM
        const menuToggle = document.getElementById('menuToggle');
        const searchInput = document.getElementById('searchInput');
        const searchClear = document.getElementById('searchClear');
        const searchSuggestions = document.getElementById('searchSuggestions');
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const userProfile = document.getElementById('userProfile');
        const userDropdown = document.getElementById('userDropdown');
        const fullscreenBtn = document.getElementById('fullscreenBtn');

        // Toggle menu latéral
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                // Dispatch custom event pour communiquer avec le sidebar
                document.dispatchEvent(new CustomEvent('toggleSidebar'));

                // Animation du bouton
                this.style.transform = 'rotate(90deg)';
                setTimeout(() => {
                    this.style.transform = 'rotate(0deg)';
                }, 200);
            });
        }

        // Gestion de la recherche
        if (searchInput) {
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                const value = this.value.trim();

                // Afficher/masquer le bouton de suppression
                if (value) {
                    searchClear.style.display = 'block';

                    // Débounce pour les suggestions
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        showSearchSuggestions(value);
                    }, 300);
                } else {
                    searchClear.style.display = 'none';
                    hideSearchSuggestions();
                }
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch(this.value.trim());
                } else if (e.key === 'Escape') {
                    hideSearchSuggestions();
                    this.blur();
                }
            });

            searchInput.addEventListener('focus', function() {
                if (this.value.trim()) {
                    showSearchSuggestions(this.value.trim());
                }
            });
        }

        // Bouton de suppression de recherche
        if (searchClear) {
            searchClear.addEventListener('click', function() {
                searchInput.value = '';
                searchInput.focus();
                this.style.display = 'none';
                hideSearchSuggestions();
            });
        }

        // Toggle notifications
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropdown(notificationDropdown);
                // Fermer le menu utilisateur si ouvert
                if (userDropdown.classList.contains('show')) {
                    userDropdown.classList.remove('show');
                }
            });
        }

        // Toggle menu utilisateur
        if (userProfile && userDropdown) {
            userProfile.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropdown(userDropdown);
                // Fermer les notifications si ouvertes
                if (notificationDropdown.classList.contains('show')) {
                    notificationDropdown.classList.remove('show');
                }
            });
        }

        // Bouton plein écran
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', function() {
                toggleFullscreen();
            });

            // Mettre à jour l'icône selon l'état
            document.addEventListener('fullscreenchange', updateFullscreenIcon);
        }

        // Fermer les dropdowns en cliquant ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.notification-wrapper')) {
                notificationDropdown?.classList.remove('show');
            }
            if (!e.target.closest('.user-profile')) {
                userDropdown?.classList.remove('show');
            }
            if (!e.target.closest('.search-container')) {
                hideSearchSuggestions();
            }
        });

        // Marquer toutes les notifications comme lues
        const markAllReadBtn = document.querySelector('.mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                markAllNotificationsAsRead();
            });
        }

        // Gestion des clics sur les notifications
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.classList.contains('unread')) {
                    markNotificationAsRead(this);
                }
            });
        });

        // Fonctions utilitaires
        function toggleDropdown(dropdown) {
            dropdown.classList.toggle('show');
        }

        function showSearchSuggestions(query) {
            if (!query) return;

            // Simuler des suggestions (à remplacer par un appel API)
            const suggestions = [
                'Utilisateurs',
                'Commandes',
                'Produits',
                'Rapports',
                'Paramètres'
            ].filter(item => item.toLowerCase().includes(query.toLowerCase()));

            if (suggestions.length > 0) {
                searchSuggestions.innerHTML = suggestions.map(suggestion =>
                    `<div class="suggestion-item" onclick="selectSuggestion('${suggestion}')">${suggestion}</div>`
                ).join('');
                searchSuggestions.style.display = 'block';
            } else {
                hideSearchSuggestions();
            }
        }

        function hideSearchSuggestions() {
            if (searchSuggestions) {
                searchSuggestions.style.display = 'none';
            }
        }

        function selectSuggestion(suggestion) {
            searchInput.value = suggestion;
            hideSearchSuggestions();
            performSearch(suggestion);
        }

        function performSearch(query) {
            if (!query) return;

            console.log('Recherche:', query);
            // Ici, implémentez votre logique de recherche
            // Par exemple, redirection vers une page de résultats
            // window.location.href = `/search?q=${encodeURIComponent(query)}`;

            // Ou dispatch d'un événement custom
            document.dispatchEvent(new CustomEvent('searchPerformed', {
                detail: { query }
            }));
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Erreur lors de l\'activation du plein écran:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }

        function updateFullscreenIcon() {
            const icon = fullscreenBtn?.querySelector('i');
            if (!icon) return;

            if (document.fullscreenElement) {
                icon.className = 'fas fa-compress';
                fullscreenBtn.title = 'Quitter le plein écran';
            } else {
                icon.className = 'fas fa-expand';
                fullscreenBtn.title = 'Plein écran';
            }
        }

        function markNotificationAsRead(notificationElement) {
            notificationElement.classList.remove('unread');
            updateNotificationBadge();

            // Envoyer une requête AJAX pour marquer comme lu côté serveur
            const notificationId = notificationElement.dataset.id;
            if (notificationId) {
                fetch('/api/notifications/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id: notificationId })
                }).catch(err => console.log('Erreur lors de la mise à jour:', err));
            }
        }

        function markAllNotificationsAsRead() {
            const unreadNotifications = document.querySelectorAll('.notification-item.unread');
            unreadNotifications.forEach(notification => {
                notification.classList.remove('unread');
            });
            updateNotificationBadge();

            // Envoyer une requête AJAX pour marquer toutes comme lues
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(err => console.log('Erreur lors de la mise à jour:', err));
        }

        function updateNotificationBadge() {
            const badge = document.querySelector('.notification-badge');
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;

            if (badge) {
                if (unreadCount === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = 'flex';
                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                }
            }
        }

        // Animations fluides pour les interactions
        function addRippleEffect(element, event) {
            const ripple = document.createElement('span');
            const rect = element.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;

            ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            pointer-events: none;
        `;

            element.style.position = 'relative';
            element.style.overflow = 'hidden';
            element.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        }

        // Ajouter l'effet ripple aux boutons
        document.querySelectorAll('.action-btn, .menu-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
                addRippleEffect(this, e);
            });
        });

        // Gestion des raccourcis clavier
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K pour focus sur la recherche
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput?.focus();
            }

            // Échap pour fermer les dropdowns
            if (e.key === 'Escape') {
                notificationDropdown?.classList.remove('show');
                userDropdown?.classList.remove('show');
                hideSearchSuggestions();
            }

            // F11 pour plein écran
            if (e.key === 'F11') {
                e.preventDefault();
                toggleFullscreen();
            }
        });

        // Gestion du thème sombre (optionnel)
        function initThemeToggle() {
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.body.classList.toggle('dark-theme');
                    const isDark = document.body.classList.contains('dark-theme');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');

                    // Mettre à jour l'icône
                    const icon = this.querySelector('i');
                    icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
                });
            }

            // Appliquer le thème sauvegardé
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
            }
        }

        // Notification en temps réel (WebSocket ou Server-Sent Events)
        function initRealTimeNotifications() {
            // Exemple avec Server-Sent Events
            if (typeof EventSource !== 'undefined') {
                const eventSource = new EventSource('/api/notifications/stream');

                eventSource.onmessage = function(event) {
                    const notification = JSON.parse(event.data);
                    addNewNotification(notification);
                };

                eventSource.onerror = function() {
                    console.log('Erreur de connexion aux notifications en temps réel');
                };
            }
        }

        function addNewNotification(notification) {
            const notificationList = document.querySelector('.notification-list');
            if (!notificationList) return;

            const notificationElement = document.createElement('div');
            notificationElement.className = 'notification-item unread';
            notificationElement.dataset.id = notification.id;
            notificationElement.innerHTML = `
            <div class="notification-icon">
                <i class="fas ${notification.icon} ${notification.iconColor}"></i>
            </div>
            <div class="notification-content">
                <p>${notification.message}</p>
                <span class="notification-time">${notification.time}</span>
            </div>
        `;

            // Ajouter en début de liste
            notificationList.insertBefore(notificationElement, notificationList.firstChild);

            // Mettre à jour le badge
            updateNotificationBadge();

            // Animation d'apparition
            notificationElement.style.opacity = '0';
            notificationElement.style.transform = 'translateX(-20px)';

            requestAnimationFrame(() => {
                notificationElement.style.transition = 'all 0.3s ease';
                notificationElement.style.opacity = '1';
                notificationElement.style.transform = 'translateX(0)';
            });

            // Ajouter l'événement de clic
            notificationElement.addEventListener('click', function() {
                if (this.classList.contains('unread')) {
                    markNotificationAsRead(this);
                }
            });
        }

        // Gestion de la déconnexion automatique
        let inactivityTimer;
        const INACTIVITY_TIME = 30 * 60 * 1000; // 30 minutes

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if (confirm('Vous êtes inactif depuis un moment. Voulez-vous rester connecté ?')) {
                    resetInactivityTimer();
                } else {
                    window.location.href = '/logout';
                }
            }, INACTIVITY_TIME);
        }

        // Événements qui réinitialisent le timer d'inactivité
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetInactivityTimer, true);
        });

        // Initialiser le timer
        resetInactivityTimer();

        // Fonctions publiques pour l'intégration
        window.DashboardHeader = {
            updatePageTitle: function(title) {
                const pageTitle = document.getElementById('pageTitle');
                if (pageTitle) {
                    pageTitle.textContent = title;
                }
            },

            showNotification: function(message, type = 'info') {
                const notification = {
                    id: Date.now(),
                    message: message,
                    type: type,
                    time: 'À l\'instant',
                    icon: type === 'success' ? 'fa-check-circle' :
                        type === 'warning' ? 'fa-exclamation-triangle' :
                            type === 'error' ? 'fa-times-circle' : 'fa-info-circle',
                    iconColor: type === 'success' ? 'text-green' :
                        type === 'warning' ? 'text-warning' :
                            type === 'error' ? 'text-danger' : 'text-blue'
                };
                addNewNotification(notification);
            },

            updateUserInfo: function(name, role, avatar = null) {
                const userNameElements = document.querySelectorAll('.user-name');
                const userRoleElements = document.querySelectorAll('.user-role');
                const userAvatars = document.querySelectorAll('.user-avatar img, .user-avatar-large img');

                userNameElements.forEach(el => el.textContent = name);
                userRoleElements.forEach(el => el.textContent = role);

                if (avatar) {
                    userAvatars.forEach(img => img.src = avatar);
                }
            }
        };

        // Initialiser les fonctionnalités optionnelles
        initThemeToggle();
        initRealTimeNotifications();

        // Ajouter les styles pour l'animation ripple
        const rippleStyles = document.createElement('style');
        rippleStyles.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Thème sombre optionnel */
        .dark-theme {
            --header-bg: #1f2937;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --text-muted: #9ca3af;
            --border-color: #374151;
            --hover-bg: #374151;
        }

        .dark-theme .search-bar {
            background: #374151;
        }

        .dark-theme .search-bar:focus-within {
            background: #4b5563;
        }

        .dark-theme .user-profile {
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        }

        .dark-theme .user-profile:hover {
            background: linear-gradient(135deg, #4b5563 0%, #6b7280 100%);
        }

        /* Styles pour les suggestions de recherche */
        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 1px solid var(--border-color);
        }

        .suggestion-item:hover {
            background: var(--hover-bg);
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        /* Amélioration de l'accessibilité */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus visible pour les utilisateurs au clavier */
        .menu-toggle:focus-visible,
        .action-btn:focus-visible,
        .user-dropdown-toggle:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
    `;
        document.head.appendChild(rippleStyles);

        console.log('Dashboard Header initialisé avec succès');
    });

    // Fonction globale pour mettre à jour le titre de la page depuis l'extérieur
    function updateHeaderTitle(title) {
        if (window.DashboardHeader) {
            window.DashboardHeader.updatePageTitle(title);
        }
    }
</script>