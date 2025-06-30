<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/index.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le tableau de bord Scolarité (proviennent du ScolariteController)
//

$scolarite_stats = $data['scolarite_stats'] ?? [
    'total_etudiants' => 1250,
    'inscriptions_actives' => 1200,
    'inscriptions_attente_paiement' => 15,
    'stages_valides' => 300,
    'notes_saisies' => 5000,
    'reclamations_en_cours' => 5,
];

$quick_links_scolarite = $data['quick_links_scolarite'] ?? [
    ['label' => 'Gérer les Étudiants', 'url' => '/personnel/scolarite/gestion-etudiants', 'icon' => 'school'],
    ['label' => 'Gérer les Inscriptions', 'url' => '/personnel/scolarite/gestion-inscriptions', 'icon' => 'how_to_reg'],
    ['label' => 'Gérer les Notes', 'url' => '/personnel/scolarite/gestion-notes', 'icon' => 'grade'],
    ['label' => 'Gérer les Stages', 'url' => '/personnel/scolarite/gestion-stages', 'icon' => 'work'],
    ['label' => 'Gérer les Pénalités', 'url' => '/personnel/scolarite/manage-penalites', 'icon' => 'gpp_bad'],
    ['label' => 'Gérer les Réclamations', 'url' => '/personnel/scolarite/liste-reclamations', 'icon' => 'feedback'],
    ['label' => 'Générer Documents', 'url' => '/personnel/scolarite/generation-documents', 'icon' => 'picture_as_pdf'],
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Tableau de Bord Scolarité</h1>
    <p class="dashboard-subtitle">Aperçu et gestion des parcours académiques.</p>

    <section class="overview-section admin-card">
        <h2 class="section-title">Statistiques Clés de Scolarité</h2>
        <div class="stats-grid">
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Total Étudiants</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">groups</span></div>
                </div>
                <p class="stat-value"><?= e($scolarite_stats['total_etudiants']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Inscriptions Actives</h3>
                    <div class="stat-icon icon-bg-green"><span class="material-icons">how_to_reg</span></div>
                </div>
                <p class="stat-value"><?= e($scolarite_stats['inscriptions_actives']); ?></p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <div class="stat-header">
                    <h3 class="stat-label">Inscriptions en Attente Paiement</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">payments</span></div>
                </div>
                <p class="stat-value"><?= e($scolarite_stats['inscriptions_attente_paiement']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Stages Validés</h3>
                    <div class="stat-icon icon-bg-violet"><span class="material-icons">work</span></div>
                </div>
                <p class="stat-value"><?= e($scolarite_stats['stages_valides']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Notes Saisies</h3>
                    <div class="stat-icon icon-bg-yellow"><span class="material-icons">grade</span></div>
                </div>
                <p class="stat-value"><?= e($scolarite_stats['notes_saisies']); ?></p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <div class="stat-header">
                    <h3 class="stat-label">Réclamations en Cours</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">feedback</span></div>
                </div>
                <p class="stat-value"><?= e($scolarite_stats['reclamations_en_cours']); ?></p>
            </div>
        </div>
    </section>

    <section class="section-quick-links-scolarite admin-card mt-xl">
        <h2 class="section-title">Accès Rapide aux Fonctionnalités</h2>
        <div class="quick-links-grid">
            <?php foreach ($quick_links_scolarite as $link): ?>
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
        // Logique JavaScript spécifique au tableau de bord Scolarité si nécessaire.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour index.php (Scolarité) */
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

    .dashboard-subtitle { /* Réutilisé de dashboard.php */
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        text-align: center;
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
    .icon-bg-violet { background-color: var(--accent-violet-light); }
    .icon-bg-yellow { background-color: var(--accent-yellow-light); }

    .dashboard-card .stat-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-top: var(--spacing-sm);
        width: 100%;
        text-align: center;
    }

    /* Grille des liens rapides (réutilisée) */
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

    .quick-action-btn span:last-child {
        text-align: center;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>