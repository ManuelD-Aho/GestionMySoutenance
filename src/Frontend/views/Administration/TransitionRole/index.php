<?php
// src/Frontend/views/Administration/TransitionRole/index.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données fictives pour les statistiques de transition de rôle
// En production, ces données proviendraient du contrôleur TransitionRoleController.
//

$stats_transition_role = $data['stats_transition_role'] ?? [
    'delegations_actives' => 5,
    'delegations_expirees_7j' => 2,
    'taches_orphelines_en_attente' => 1,
    'transitions_recentes_30j' => 8,
];

$liens_transition_role = [
    ['label' => 'Créer une Délégation', 'url' => '/admin/transition-role/delegations/create', 'icon' => 'person_add_alt_1', 'description' => 'Transférer temporairement des responsabilités.'],
    ['label' => 'Voir les Délégations', 'url' => '/admin/transition-role/delegations', 'icon' => 'list_alt', 'description' => 'Consulter et gérer les délégations actives et passées.'],
    ['label' => 'Gérer les Tâches Orphelines', 'url' => '/admin/transition-role/taches-orphelines', 'icon' => 'assignment_late', 'description' => 'Réaffecter les tâches non attribuées après un départ/changement de rôle.'],
    // Vous pourriez avoir d'autres liens ici, par ex. pour des rapports sur les transitions
    // ['label' => 'Rapports sur les Transitions', 'url' => '/admin/transition-role/reports', 'icon' => 'analytics'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Délégations et Transitions de Rôles</h1>

    <section class="overview-section admin-card">
        <h2 class="section-title">Aperçu des Transitions de Rôles</h2>
        <div class="stats-grid">
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Délégations Actives</h3>
                    <div class="stat-icon icon-bg-green"><span class="material-icons">how_to_reg</span></div>
                </div>
                <p class="stat-value"><?= e($stats_transition_role['delegations_actives']); ?></p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <div class="stat-header">
                    <h3 class="stat-label">Tâches Orphelines</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">assignment_late</span></div>
                </div>
                <p class="stat-value"><?= e($stats_transition_role['taches_orphelines_en_attente']); ?></p>
                <p class="stat-change negative"><span class="material-icons">priority_high</span>À réaffecter</p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Délégations Expirées (7j)</h3>
                    <div class="stat-icon icon-bg-yellow"><span class="material-icons">event_busy</span></div>
                </div>
                <p class="stat-value"><?= e($stats_transition_role['delegations_expirees_7j']); ?></p>
                <p class="stat-change neutral"><span class="material-icons">history</span>Récemment terminées</p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Transitions Récentes (30j)</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">swap_horiz</span></div>
                </div>
                <p class="stat-value"><?= e($stats_transition_role['transitions_recentes_30j']); ?></p>
            </div>
        </div>
    </section>

    <section class="section-quick-links admin-card mt-xl">
        <h2 class="section-title">Actions Rapides</h2>
        <div class="quick-links-grid transition-role-links-grid">
            <?php foreach ($liens_transition_role as $lien): ?>
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
        // Logique JavaScript spécifique à cette page d'index de transition de rôle si nécessaire.
        // Pour l'instant, c'est principalement une page de navigation et de résumé.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour index.php (TransitionRole) */
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
    .dashboard-card .stat-change.positive { color: var(--success-color); }


    /* Grille des liens rapides */
    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

    .quick-action-btn span:last-child {
        text-align: center;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
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