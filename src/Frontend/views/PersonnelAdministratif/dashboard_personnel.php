<?php
// src/Frontend/views/PersonnelAdministratif/dashboard_personnel.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le tableau de bord du personnel (proviennent du PersonnelDashboardController)
//

$personnel_data = $data['personnel_data'] ?? [
    'prenom' => 'Claire',
    'nom' => 'Durand',
    'fonction' => 'Responsable Scolarité', // Ou 'Agent de Contrôle de Conformité'
    'last_login' => '2025-06-30 15:00:00',
    'is_rs' => true, // Indicateur de rôle pour adapter l'affichage
    'is_agent_conformite' => false,
];

// Données spécifiques au rôle (simulées)
$stats_conformite = [
    'rapports_a_verifier' => 5,
    'rapports_traites_aujourdhui' => 12,
];

$stats_scolarite = [
    'inscriptions_attente_paiement' => 3,
    'etudiants_eligibles_activation_compte' => 7,
    'stages_en_attente_validation' => 2,
    'reclamations_ouvertes' => 4,
];

$recent_notifications = $data['recent_notifications'] ?? [
    ['id' => 1, 'message' => 'Nouveau rapport à vérifier : RAP-2025-0053.', 'type' => 'info', 'date' => '2025-06-30 16:00'],
    ['id' => 2, 'message' => 'Inscription de Marie Dupont en attente de validation du paiement.', 'type' => 'warning', 'date' => '2025-06-30 15:30'],
];

$quick_links_personnel = [
    ['label' => 'Mon Profil', 'url' => '/profile', 'icon' => 'account_circle'],
    ['label' => 'Messagerie Interne', 'url' => '/chat', 'icon' => 'chat'],
    ['label' => 'Documents Administratifs', 'url' => '/personnel/documents/generate', 'icon' => 'picture_as_pdf'],
    ['label' => 'Aide & Support', 'url' => '/help', 'icon' => 'help'],
];

// Liens conditionnels
if ($personnel_data['is_agent_conformite']) {
    $quick_links_personnel[] = ['label' => 'Rapports à Vérifier', 'url' => '/personnel/conformite/rapports-a-verifier', 'icon' => 'rule'];
    $quick_links_personnel[] = ['label' => 'Historique Conformité', 'url' => '/personnel/conformite/rapports-traites', 'icon' => 'history'];
}
if ($personnel_data['is_rs']) {
    $quick_links_personnel[] = ['label' => 'Gestion Étudiants', 'url' => '/personnel/scolarite/gestion-etudiants', 'icon' => 'school'];
    $quick_links_personnel[] = ['label' => 'Gestion Inscriptions', 'url' => '/personnel/scolarite/gestion-inscriptions', 'icon' => 'how_to_reg'];
    $quick_links_personnel[] = ['label' => 'Gestion Notes', 'url' => '/personnel/scolarite/gestion-notes', 'icon' => 'grade'];
    $quick_links_personnel[] = ['label' => 'Gestion Stages', 'url' => '/personnel/scolarite/gestion-stages', 'icon' => 'work'];
    $quick_links_personnel[] = ['label' => 'Gérer Pénalités', 'url' => '/personnel/scolarite/manage-penalites', 'icon' => 'gpp_bad'];
    $quick_links_personnel[] = ['label' => 'Réclamations Étudiantes', 'url' => '/personnel/scolarite/liste-reclamations', 'icon' => 'feedback'];
}

?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Tableau de Bord Personnel Administratif</h1>
    <p class="dashboard-subtitle">Bienvenue, <?= e($personnel_data['prenom']) . ' ' . e($personnel_data['nom']); ?> (<?= e($personnel_data['fonction']); ?>)</p>
    <p class="last-login-info">Dernière connexion : <?= e(date('d/m/Y H:i', strtotime($personnel_data['last_login']))); ?></p>

    <section class="overview-section admin-card">
        <h2 class="section-title">Aperçu des Tâches</h2>
        <div class="stats-grid">
            <?php if ($personnel_data['is_agent_conformite']): ?>
                <div class="dashboard-card stat-card alert-card">
                    <div class="stat-header">
                        <h3 class="stat-label">Rapports à Vérifier</h3>
                        <div class="stat-icon icon-bg-red"><span class="material-icons">assignment_turned_in</span></div>
                    </div>
                    <p class="stat-value"><?= e($stats_conformite['rapports_a_verifier']); ?></p>
                    <p class="stat-change negative"><span class="material-icons">rule</span> Conformité</p>
                </div>
                <div class="dashboard-card stat-card">
                    <div class="stat-header">
                        <h3 class="stat-label">Rapports Traités Aujourd'hui</h3>
                        <div class="stat-icon icon-bg-green"><span class="material-icons">check_circle</span></div>
                    </div>
                    <p class="stat-value"><?= e($stats_conformite['rapports_traites_aujourdhui']); ?></p>
                    <p class="stat-change positive"><span class="material-icons">history</span> Vérifications</p>
                </div>
            <?php endif; ?>

            <?php if ($personnel_data['is_rs']): ?>
                <div class="dashboard-card stat-card alert-card">
                    <div class="stat-header">
                        <h3 class="stat-label">Inscriptions en Attente Paiement</h3>
                        <div class="stat-icon icon-bg-red"><span class="material-icons">payments</span></div>
                    </div>
                    <p class="stat-value"><?= e($stats_scolarite['inscriptions_attente_paiement']); ?></p>
                    <p class="stat-change negative"><span class="material-icons">account_balance_wallet</span> Scolarité</p>
                </div>
                <div class="dashboard-card stat-card">
                    <div class="stat-header">
                        <h3 class="stat-label">Étudiants à Activer Compte</h3>
                        <div class="stat-icon icon-bg-blue"><span class="material-icons">person_add</span></div>
                    </div>
                    <p class="stat-value"><?= e($stats_scolarite['etudiants_eligibles_activation_compte']); ?></p>
                    <p class="stat-change neutral"><span class="material-icons">how_to_reg</span> Accès plateforme</p>
                </div>
                <div class="dashboard-card stat-card">
                    <div class="stat-header">
                        <h3 class="stat-label">Stages à Valider</h3>
                        <div class="stat-icon icon-bg-yellow"><span class="material-icons">work</span></div>
                    </div>
                    <p class="stat-value"><?= e($stats_scolarite['stages_en_attente_validation']); ?></p>
                    <p class="stat-change neutral"><span class="material-icons">assignment</span> Conformité stage</p>
                </div>
                <div class="dashboard-card stat-card alert-card">
                    <div class="stat-header">
                        <h3 class="stat-label">Réclamations Ouvertes</h3>
                        <div class="stat-icon icon-bg-red"><span class="material-icons">feedback</span></div>
                    </div>
                    <p class="stat-value"><?= e($stats_scolarite['reclamations_ouvertes']); ?></p>
                    <p class="stat-change negative"><span class="material-icons">priority_high</span> Urgence</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section-notifications-dashboard admin-card mt-xl">
        <h2 class="section-title">Notifications Récentes</h2>
        <?php if (!empty($recent_notifications)): ?>
            <ul class="notifications-list">
                <?php foreach ($recent_notifications as $notif): ?>
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
            <div class="text-center mt-lg">
                <a href="/dashboard/notifications" class="link-secondary">Voir toutes les notifications</a>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune notification récente.</p>
        <?php endif; ?>
    </section>

    <section class="section-quick-links-personnel admin-card mt-xl">
        <h2 class="section-title">Accès Rapide aux Fonctionnalités</h2>
        <div class="quick-links-grid common-links-grid">
            <?php foreach ($quick_links_personnel as $link): ?>
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
        // Logique JavaScript spécifique au tableau de bord du personnel si nécessaire.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour dashboard_personnel.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1200px;
        margin: var(--spacing-xl) auto;
    }

    .dashboard-title { /* Réutilisé de dashboard.php */
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px solid var(--border-light);
    }

    .dashboard-subtitle, .last-login-info { /* Réutilisé de dashboard.php */
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        text-align: center;
        margin-bottom: var(--spacing-sm);
    }
    .last-login-info {
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-xl);
    }

    .admin-card { /* Réutilisé des modules d'administration */
        background-color: var(--bg-secondary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .section-title { /* Réutilisé des formulaires admin */
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* Grille de statistiques (réutilisée du dashboard admin) */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .dashboard-card {
        background-color: var(--primary-white);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--border-light);
    }

    .dashboard-card .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: var(--spacing-sm);
    }

    .dashboard-card .stat-label {
        font-size: var(--font-size-lg);
        color: var(--text-secondary);
        font-weight: var(--font-weight-medium);
        text-align: left;
        flex-grow: 1;
    }

    .dashboard-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-2xl);
        color: var(--text-white);
    }

    /* Couleurs des icônes de statistiques (réutilisées de root.css) */
    .icon-bg-blue { background-color: var(--primary-blue-light); }
    .icon-bg-green { background-color: var(--primary-green-light); }
    .icon-bg-red { background-color: var(--accent-red-light); }
    .icon-bg-yellow { background-color: var(--accent-yellow-light); }
    .icon-bg-violet { background-color: var(--accent-violet-light); }


    .dashboard-card .stat-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-top: var(--spacing-sm);
        width: 100%;
        text-align: center;
    }

    .dashboard-card .stat-change {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-top: var(--spacing-xs);
        width: 100%;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
    }
    .dashboard-card .stat-change.negative { color: var(--accent-red); }
    .dashboard-card .stat-change.neutral { color: var(--text-secondary); }
    .dashboard-card .stat-change.positive { color: var(--primary-green); }


    /* Notifications sur le dashboard (réutilisé du common/notifications_panel.php) */
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


    .notification-content { flex-grow: 1; }
    .notification-message {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
    }
    .notification-date {
        font-size: var(--font-size-sm);
        color: var(--text-light);
    }

    /* Grille de liens rapides (réutilisée du common/dashboard.php) */
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
        min-height: 120px;
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

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
</style>