<header class="main-header">
    <div class="header-left">
        <a href="/dashboard" class="logo">GestionMySoutenance</a>
    </div>
    <div class="header-right">
        <div class="user-info">
            <?php if (isset($current_user) && $current_user) : ?>
                <span>Bonjour, <?php echo htmlspecialchars($current_user['profil']['prenom'] ?? $current_user['login_utilisateur']); ?></span>
            <?php endif; ?>
        </div>
        <div class="header-actions">
            <a href="/dashboard/notifications" class="notification-icon">
                <i class="fas fa-bell"></i>
                <?php
                // Suppose que le contrôleur du dashboard passe 'notifications_non_lues_count'
                // Ou vous devez le récupérer ici via une AJAX ou une variable passée spécifiquement au header
                // Si vous avez un nombre de notifications non lues
                if (isset($notifications_non_lues_count) && $notifications_non_lues_count > 0) {
                    echo '<span class="badge">' . htmlspecialchars($notifications_non_lues_count) . '</span>';
                }
                ?>
            </a>
            <a href="/logout" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</header>
