<?php
// src/Frontend/views/common/dashboard.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le tableau de bord commun (proviennent du DashboardController)
//

$user_info = $data['user_info'] ?? [
    'prenom' => 'Utilisateur',
    'nom' => 'Générique',
    'type_compte' => 'Visiteur',
    'last_login' => '2025-06-30 10:00:00',
];

$common_notifications = $data['common_notifications'] ?? [
    ['id' => 1, 'message' => 'Bienvenue sur GestionMySoutenance !', 'type' => 'info', 'date' => '2025-06-30 09:00'],
    ['id' => 2, 'message' => 'Nouveaux guides disponibles dans la section Aide.', 'type' => 'success', 'date' => '2025-06-29 14:00'],
];

$quick_links_common = $data['quick_links_common'] ?? [
    ['label' => 'Mon Profil', 'url' => '/profile', 'icon' => 'account_circle'],
    ['label' => 'Ma Messagerie', 'url' => '/chat', 'icon' => 'chat'],
    ['label' => 'Mes Documents', 'url' => '/documents', 'icon' => 'folder_shared'],
    ['label' => 'Aide & Support', 'url' => '/help', 'icon' => 'help_outline'],
];

?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Bienvenue, <?= e($user_info['prenom']) . ' ' . e($user_info['nom']); ?> !</h1>
    <p class="dashboard-subtitle">Vous êtes connecté(e) en tant que <?= e($user_info['type_compte']); ?>.</p>
    <p class="last-login-info">Dernière connexion : <?= e(date('d/m/Y H:i', strtotime($user_info['last_login']))); ?></p>

    <section class="section-notifications admin-card mt-xl">
        <h2 class="section-title">Notifications Générales</h2>
        <?php if (!empty($common_notifications)): ?>
            <ul class="notifications-list">
                <?php foreach ($common_notifications as $notif): ?>
                    <li class="notification-item notification-<?= e($notif['type']); ?>">
                        <span class="material-icons icon-<?= e($notif['type']); ?>">
                            <?php
                            if ($notif['type'] === 'info') echo 'info';
                            elseif ($notif['type'] === 'success') echo 'check_circle';
                            elseif ($notif['type'] === 'warning') echo 'warning';
                            elseif ($notif['type'] === 'error') echo 'error';
                            else echo 'notifications';
                            ?>
                        </span>
                        <div class="notification-content">
                            <p class="notification-message"><?= e($notif['message']); ?></p>
                            <span class="notification-date"><?= e(date('d/m/Y H:i', strtotime($notif['date']))); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucune notification générale récente.</p>
        <?php endif; ?>
    </section>

    <section class="section-quick-links admin-card mt-xl">
        <h2 class="section-title">Accès Rapide</h2>
        <div class="quick-links-grid common-links-grid">
            <?php foreach ($quick_links_common as $link): ?>
                <a href="<?= e($link['url']); ?>" class="quick-action-btn">
                    <span class="material-icons"><?= e($link['icon']); ?></span>
                    <span><?= e($link['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript spécifique au tableau de bord commun si nécessaire.
        // Pour l'instant, c'est principalement une page de résumé et de navigation.

        // Gestion de l'affichage des messages flash (si votre système en utilise)
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour dashboard.php (common) */
    /* Réutilisation des classes de root.css, admin_module.css et dashboard_style.css */

    body {
        background-color: var(--bg-secondary); /* Fond du corps du document */
    }

    .common-dashboard-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1000px; /* Taille modérée pour un tableau de bord générique */
        margin: var(--spacing-xl) auto; /* Centrage et espacement */
    }

    .dashboard-title {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px solid var(--border-light);
    }

    .dashboard-subtitle, .last-login-info {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        text-align: center;
        margin-bottom: var(--spacing-sm);
    }
    .last-login-info {
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-xl); /* Plus d'espace sous les infos de connexion */
    }

    .admin-card { /* Réutilisé des modules d'administration */
        background-color: var(--bg-secondary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* Notifications (réutilisées du dashboard_commission.php) */
    .notifications-list {
        list-style: none;
        padding: 0;
    }

    .notification-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-sm) var(--spacing-md);
        margin-bottom: var(--spacing-sm);
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-sm);
        box-shadow: var(--shadow-sm);
    }

    .notification-item .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }
    .notification-item .icon-info { color: var(--info-color); }
    .notification-item .icon-success { color: var(--success-color); }
    .notification-item .icon-warning { color: var(--warning-color); }
    .notification-item .icon-error { color: var(--error-color); }
    .notification-item .icon-notifications { color: var(--text-secondary); }

    .notification-content {
        flex-grow: 1;
    }

    .notification-message {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
    }

    .notification-date {
        font-size: var(--font-size-sm);
        color: var(--text-light);
    }

    /* Grille des liens rapides (réutilisés) */
    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-lg);
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        text-decoration: none;
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        transition: all var(--transition-fast);
        cursor: pointer;
        min-height: 120px; /* Ajusté pour moins de contenu */
    }

    .quick-action-btn:hover {
        background-color: var(--primary-blue-light);
        color: var(--text-white);
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .quick-action-btn:hover .material-icons {
        color: var(--text-white);
    }

    .quick-action-btn .material-icons {
        font-size: var(--font-size-4xl);
        color: var(--primary-blue);
        margin-bottom: var(--spacing-sm);
        transition: color var(--transition-fast);
    }

    .quick-action-btn span:last-child {
        text-align: center;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
    }

    /* Widgets (futur) */
    .widgets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-md);
    }

    .widget-card {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>