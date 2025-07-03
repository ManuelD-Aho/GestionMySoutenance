<?php
// src/Frontend/views/Administration/Habilitations/index.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données fictives pour les statistiques et les liens rapides du module Habilitations
// En production, ces données proviendraient du contrôleur HabilitationController.
//

$stats_habilitations = $data['stats_habilitations'] ?? [
    'total_groupes' => 8,
    'total_traitements' => 150,
    'total_niveaux_acces' => 5,
    'total_types_utilisateur' => 4,
    'rattachements_actifs' => 1200, // Ex: liens entre groupes et traitements
];

$liens_habilitations = [
    ['label' => 'Gérer les Groupes', 'url' => '/admin/habilitations/groupes', 'icon' => 'group_work', 'description' => 'Définir les rôles fonctionnels de l\'application.'],
    ['label' => 'Gérer les Traitements', 'url' => '/admin/habilitations/traitements', 'icon' => 'build', 'description' => 'Administrer les actions atomiques protégées du système.'],
    ['label' => 'Gérer les Niveaux d\'Accès', 'url' => '/admin/habilitations/niveaux-acces', 'icon' => 'lock_open', 'description' => 'Définir la granularité d\'accès aux données.'],
    ['label' => 'Gérer les Types d\'Utilisateur', 'url' => '/admin/habilitations/types-utilisateur', 'icon' => 'category', 'description' => 'Classifier les grandes catégories d\'utilisateurs.'],
    ['label' => 'Gestion des Rattachements', 'url' => '/admin/habilitations/rattachements', 'icon' => 'link', 'description' => 'Lier groupes aux traitements pour définir les permissions.'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Accueil des Habilitations et Permissions</h1>

    <section class="overview-section admin-card">
        <h2 class="section-title">Aperçu du Contrôle d'Accès</h2>
        <div class="stats-grid">
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Total Groupes</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">groups</span></div>
                </div>
                <p class="stat-value"><?= e($stats_habilitations['total_groupes']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Total Traitements</h3>
                    <div class="stat-icon icon-bg-green"><span class="material-icons">extension</span></div>
                </div>
                <p class="stat-value"><?= e($stats_habilitations['total_traitements']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Niveaux d'Accès</h3>
                    <div class="stat-icon icon-bg-violet"><span class="material-icons">security</span></div>
                </div>
                <p class="stat-value"><?= e($stats_habilitations['total_niveaux_acces']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Types Utilisateurs</h3>
                    <div class="stat-icon icon-bg-yellow"><span class="material-icons">person</span></div>
                </div>
                <p class="stat-value"><?= e($stats_habilitations['total_types_utilisateur']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Rattachements Actifs</h3>
                    <div class="stat-icon icon-bg-blue-light"><span class="material-icons">link</span></div>
                </div>
                <p class="stat-value"><?= e($stats_habilitations['rattachements_actifs']); ?></p>
            </div>
        </div>
    </section>

    <section class="section-quick-links admin-card mt-xl">
        <h2 class="section-title">Accès Rapide aux Configurations</h2>
        <div class="quick-links-grid habilitations-links-grid">
            <?php foreach ($liens_habilitations as $lien): ?>
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
        // Logique JavaScript spécifique à cette page d'index des habilitations si nécessaire.
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
    /* Styles spécifiques pour index.php (Habilitations) */
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
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Plus compact pour plus de cartes */
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
        border: 1px solid var(--border-light); /* Ajouter une bordure subtile */
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
        width: 40px; /* Légèrement plus petit */
        height: 40px;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl); /* Taille icône plus petite */
        color: var(--text-white);
    }

    /* Couleurs des icônes de statistiques */
    .icon-bg-blue { background-color: var(--primary-blue-light); }
    .icon-bg-green { background-color: var(--primary-green-light); }
    .icon-bg-violet { background-color: var(--accent-violet-light); }
    .icon-bg-yellow { background-color: var(--accent-yellow-light); }
    /* Nouvelle couleur pour icon-bg-blue-light */
    .icon-bg-blue-light { background-color: var(--primary-blue-light); }


    .dashboard-card .stat-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-top: var(--spacing-sm);
        width: 100%;
        text-align: center;
    }

    /* Grille des liens rapides */
    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Plus de largeur pour la description */
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
        min-height: 150px; /* Hauteur minimale pour la consistance */
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
        font-size: var(--font-size-lg); /* Plus grande pour les titres */
        font-weight: var(--font-weight-semibold);
    }

    .link-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        text-align: center;
        margin-top: var(--spacing-xs);
        line-height: var(--line-height-tight);
        min-height: 40px; /* Assurer un espace pour la description */
    }

    .quick-action-btn:hover .link-description {
        color: var(--text-white);
    }

    /* Utilitaires généraux */
    .mt-xl { margin-top: var(--spacing-xl); }
</style>