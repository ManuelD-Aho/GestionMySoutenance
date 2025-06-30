<?php
// src/Frontend/views/common/header.php

// Récupérer les données utilisateur et le nombre de notifications depuis la session
$currentUser = $_SESSION['user_data'] ?? null;
$userName = 'Utilisateur';
$userRole = 'Rôle';
$userEmail = 'email@example.com';
$userAvatar = null;
$notificationCount = $_SESSION['notification_count'] ?? 0; // Ce nombre devrait être mis à jour par un contrôleur ou un service

if ($currentUser) {
    // Assurez-vous que les champs sont présents dans $currentUser (depuis la BDD)
    $userName = htmlspecialchars($currentUser['prenom'] ?? '') . ' ' . htmlspecialchars($currentUser['nom'] ?? '');
    $userEmail = htmlspecialchars($currentUser['email_principal'] ?? 'email@example.com');
    $userAvatar = htmlspecialchars($currentUser['photo_profil'] ?? ''); // Chemin vers la photo de profil, si non vide
    // Récupérer le libellé du rôle depuis la session ou les données utilisateur
    $userRole = htmlspecialchars($_SESSION['user_role_label'] ?? ($currentUser['id_type_utilisateur'] ?? 'Rôle Inconnu')); // id_type_utilisateur pour fallback
}

$logoutUrl = '/logout'; // L'URL de déconnexion
?>

<header class="dashboard-header">
    <div class="dashboard-header-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu" title="Afficher/Masquer le menu">
            <span class="material-icons">menu</span>
        </button>
        <div class="header-title">
            <h1 class="page-title" id="pageTitle">Chargement...</h1>
        </div>
    </div>

    <div class="dashboard-header-center">
        <div class="search-container">
            <div class="search-bar" id="searchBar">
                <span class="material-icons search-icon">search</span>
                <input type="text"
                       placeholder="Rechercher..."
                       class="search-input"
                       id="searchInput"
                       autocomplete="off">
                <button class="search-clear" id="searchClear" style="display: none;" title="Effacer la recherche">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="search-suggestions" id="searchSuggestions" style="display: none;">
            </div>
        </div>
    </div>

    <div class="dashboard-header-right">
        <div class="header-actions">
            <button class="action-btn" id="fullscreenBtn" aria-label="Plein écran" title="Activer le mode plein écran">
                <span class="material-icons">fullscreen</span>
            </button>

            <div class="notification-wrapper">
                <button class="action-btn notification-btn" id="notificationBtn" aria-label="Notifications" title="Voir les notifications">
                    <span class="material-icons">notifications</span>
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

            <button class="action-btn" id="settingsBtn" aria-label="Paramètres" title="Accéder aux paramètres">
                <span class="material-icons">settings</span>
            </button>
        </div>

        <div class="user-profile" id="userProfile">
            <div class="user-avatar">
                <?php if ($userAvatar): ?>
                    <img src="<?php echo $userAvatar; ?>" alt="Avatar de <?php echo $userName; ?>">
                <?php else: ?>
                    <div class="avatar-placeholder" title="Avatar de <?php echo $userName; ?>">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="status-indicator online"></div>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo $userName; ?></div>
                <div class="user-role"><?php echo $userRole; ?></div>
            </div>
            <button class="user-dropdown-toggle" id="userDropdownToggle" aria-label="Menu utilisateur" title="Ouvrir le menu utilisateur">
                <span class="material-icons">expand_more</span>
            </button>

            <div class="user-dropdown" id="userDropdown">
                <div class="dropdown-header">
                    <div class="user-avatar-large">
                        <?php if ($userAvatar): ?>
                            <img src="<?php echo $userAvatar; ?>" alt="Avatar de <?php echo $userName; ?>">
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
                        <span class="material-icons">account_circle</span>
                        <span>Mon Profil</span>
                    </a>
                    <a href="/dashboard/settings" class="dropdown-item">
                        <span class="material-icons">settings</span>
                        <span>Paramètres</span>
                    </a>
                    <a href="/dashboard/help" class="dropdown-item">
                        <span class="material-icons">help_outline</span>
                        <span>Aide</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo $logoutUrl; ?>" class="dropdown-item logout" id="logoutBtn">
                        <span class="material-icons">logout</span>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>