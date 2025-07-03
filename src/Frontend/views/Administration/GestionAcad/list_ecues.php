<?php
// src/Frontend/views/Administration/GestionAcad/index.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données fictives pour les statistiques du module et les liens rapides
// En production, ces données proviendraient du contrôleur GestionAcadController.
//

$stats_gestion_acad = $data['stats_gestion_acad'] ?? [
    'total_etudiants_inscrits' => 1250,
    'total_ues' => 50,
    'total_ecues' => 180,
    'stages_en_cours' => 320,
    'inscriptions_en_attente_paiement' => 15,
];

$liens_gestion_acad = [
    ['label' => 'Gérer les Inscriptions', 'url' => '/admin/gestion-acad/inscriptions', 'icon' => 'how_to_reg'],
    ['label' => 'Gérer les Notes', 'url' => '/admin/gestion-acad/notes', 'icon' => 'grade'],
    ['label' => 'Gérer les Stages', 'url' => '/admin/gestion-acad/stages', 'icon' => 'work'],
    ['label' => 'Gérer les UEs', 'url' => '/admin/gestion-acad/ues', 'icon' => 'menu_book'],
    ['label' => 'Gérer les ECUEs', 'url' => '/admin/gestion-acad/ecues', 'icon' => 'auto_stories'],
    ['label' => 'Générer Documents Scolarité', 'url' => '/personnel/scolarite/generation-documents', 'icon' => 'description'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Accueil de la Gestion Académique</h1>

    <section class="overview-section admin-card">
        <h2 class="section-title">Aperçu Général</h2>
        <div class="stats-grid">
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Étudiants Inscrits</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">school</span></div>
                </div>
                <p class="stat-value"><?= e($stats_gestion_acad['total_etudiants_inscrits']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">UEs Actives</h3>
                    <div class="stat-icon icon-bg-green"><span class="material-icons">menu_book</span></div>
                </div>
                <p class="stat-value"><?= e($stats_gestion_acad['total_ues']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">ECUEs Enregistrés</h3>
                    <div class="stat-icon icon-bg-violet"><span class="material-icons">auto_stories</span></div>
                </div>
                <p class="stat-value"><?= e($stats_gestion_acad['total_ecues']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Stages en Cours</h3>
                    <div class="stat-icon icon-bg-yellow"><span class="material-icons">work</span></div>
                </div>
                <p class="stat-value"><?= e($stats_gestion_acad['stages_en_cours']); ?></p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <div class="stat-header">
                    <h3 class="stat-label">Inscriptions en Attente Paiement</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">payments</span></div>
                </div>
                <p class="stat-value"><?= e($stats_gestion_acad['inscriptions_en_attente_paiement']); ?></p>
            </div>
        </div>
    </section>

    <section class="section-quick-links admin-card mt-xl">
        <h2 class="section-title">Accès Rapide aux Fonctionnalités</h2>
        <div class="quick-links-grid">
            <?php foreach ($liens_gestion_acad as $lien): ?>
                <a href="<?= e($lien['url']); ?>" class="quick-action-btn">
                    <span class="material-icons"><?= e($lien['icon']); ?></span>
                    <span><?= e($lien['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript spécifique à cette page d'index si nécessaire.
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
    /* Styles spécifiques pour index.php (GestionAcad) */
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

    /* Couleurs des icônes de statistiques */
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
    }

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
    }

    /* Utilitaires généraux */
    .mt-xl { margin-top: var(--spacing-xl); }
</style>