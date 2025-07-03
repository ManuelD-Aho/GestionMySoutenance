<?php
// src/Frontend/views/Administration/Supervision/index.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données fictives pour les statistiques de supervision
// En production, ces données proviendraient du contrôleur SupervisionController.
//

$stats_supervision = $data['stats_supervision'] ?? [
    'erreurs_critiques_24h' => 5,
    'avertissements_24h' => 20,
    'taches_queue_en_attente' => 15,
    'taches_queue_echouees' => 3,
    'total_logs_journalises_7j' => 15000,
    'derniere_sauvegarde' => '2025-06-30 02:00:00',
    'status_base_donnees' => 'Opérationnel',
    'status_serveur_web' => 'Opérationnel (charge élevée)',
];

$liens_supervision = [
    ['label' => 'Journaux d\'Audit', 'url' => '/admin/supervision/journaux-audit', 'icon' => 'history_toggle_off', 'description' => 'Consulter les actions critiques des utilisateurs.'],
    ['label' => 'Journaux d\'Erreurs (Logs)', 'url' => '/admin/supervision/logs', 'icon' => 'bug_report', 'description' => 'Diagnostiquer les erreurs techniques du système.'],
    ['label' => 'Gestion de la File d\'Attente', 'url' => '/admin/supervision/queue', 'icon' => 'queue', 'description' => 'Surveiller et gérer les tâches asynchrones.'],
    ['label' => 'Maintenance Système', 'url' => '/admin/supervision/maintenance', 'icon' => 'build', 'description' => 'Effectuer les opérations de maintenance (sauvegarde, restauration).'],
    ['label' => 'Suivi des Workflows', 'url' => '/admin/supervision/suivi-workflows', 'icon' => 'flowchart', 'description' => 'Visualiser l\'état d\'avancement des processus métier.'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Tableau de Bord de Supervision</h1>

    <section class="overview-section admin-card">
        <h2 class="section-title">État Général du Système</h2>
        <div class="stats-grid">
            <div class="dashboard-card stat-card status-indicator-large status-<?= $stats_supervision['status_base_donnees'] === 'Opérationnel' ? 'healthy' : 'error'; ?>">
                <div class="stat-header">
                    <h3 class="stat-label">Base de Données</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">storage</span></div>
                </div>
                <p class="stat-value"><?= e($stats_supervision['status_base_donnees']); ?></p>
            </div>
            <div class="dashboard-card stat-card status-indicator-large status-<?= strpos($stats_supervision['status_serveur_web'], 'charge élevée') !== false ? 'warning' : 'healthy'; ?>">
                <div class="stat-header">
                    <h3 class="stat-label">Serveur Web</h3>
                    <div class="stat-icon icon-bg-green"><span class="material-icons">cloud</span></div>
                </div>
                <p class="stat-value"><?= e($stats_supervision['status_serveur_web']); ?></p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <div class="stat-header">
                    <h3 class="stat-label">Erreurs Critiques (24h)</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">error</span></div>
                </div>
                <p class="stat-value"><?= e($stats_supervision['erreurs_critiques_24h']); ?></p>
                <p class="stat-change negative"><span class="material-icons">warning</span>Avertissements: <?= e($stats_supervision['avertissements_24h']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Tâches en File d'Attente</h3>
                    <div class="stat-icon icon-bg-violet"><span class="material-icons">queue</span></div>
                </div>
                <p class="stat-value"><?= e($stats_supervision['taches_queue_en_attente']); ?></p>
                <p class="stat-change negative"><span class="material-icons">cancel</span>Échouées: <?= e($stats_supervision['taches_queue_echouees']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Dernière Sauvegarde</h3>
                    <div class="stat-icon icon-bg-yellow"><span class="material-icons">backup</span></div>
                </div>
                <p class="stat-value"><?= e(date('d/m/Y H:i', strtotime($stats_supervision['derniere_sauvegarde']))); ?></p>
                <p class="stat-change neutral"><span class="material-icons">check</span>À jour</p>
            </div>
        </div>
    </section>

    <section class="section-quick-links admin-card mt-xl">
        <h2 class="section-title">Accès Rapide aux Outils</h2>
        <div class="quick-links-grid supervision-links-grid">
            <?php foreach ($liens_supervision as $lien): ?>
                <a href="<?= e($lien['url']); ?>" class="quick-action-btn">
                    <span class="material-icons"><?= e($lien['icon']); ?></span>
                    <span><?= e($lien['label']); ?></span>
                    <p class="link-description"><?= e($lien['description']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript spécifique à cette page d'index de supervision si nécessaire.
        // Par exemple, pour des mises à jour en temps réel via AJAX ou WebSocket.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour index.php (Supervision) */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1200px;
        margin: var(--spacing-xl) auto;
    }

    .admin-title {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-sm);
        border-bottom: 1px solid var(--border-light);
    }

    .admin-card {
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

    /* Grille de statistiques (réutilisation des styles du dashboard principal) */
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

    /* Couleurs des icônes de statistiques (réutilisées de dashboard_admin.php et root.css) */
    .icon-bg-blue { background-color: var(--primary-blue-light); }
    .icon-bg-green { background-color: var(--primary-green-light); }
    .icon-bg-violet { background-color: var(--accent-violet-light); }
    .icon-bg-yellow { background-color: var(--accent-yellow-light); }
    .icon-bg-red { background-color: var(--accent-red-light); }

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

    /* Indicateurs de statut général des cartes */
    .status-indicator-large {
        border-left: var(--border-width-thick) solid;
        padding-left: var(--spacing-md); /* Espace pour le trait de couleur */
        text-align: left;
    }
    .status-indicator-large.status-healthy { border-color: var(--primary-green); }
    .status-indicator-large.status-warning { border-color: var(--accent-yellow); }
    .status-indicator-large.status-error { border-color: var(--accent-red); }


    /* Grille des liens rapides */
    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        min-height: 150px;
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

    .link-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        text-align: center;
        margin-top: var(--spacing-xs);
        line-height: var(--line-height-tight);
        min-height: 40px;
    }

    .quick-action-btn:hover .link-description {
        color: var(--text-white);
    }

    /* Utilitaires */
    .mt-xl { margin-top: var(--spacing-xl); }
</style>