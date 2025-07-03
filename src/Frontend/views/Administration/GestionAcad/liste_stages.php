<?php
// src/Frontend/views/Administration/GestionAcad/liste_stages.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les stages (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//

$stages_enregistres = $data['stages_enregistres'] ?? [
    ['id' => 1, 'etudiant_nom' => 'Dupont Jean', 'entreprise_nom' => 'Tech Solutions Corp', 'type_stage' => 'Fin d\'études', 'date_debut' => '2025-01-15', 'date_fin' => '2025-06-15', 'valide' => true],
    ['id' => 2, 'etudiant_nom' => 'Curie Marie', 'entreprise_nom' => 'Innovate France', 'type_stage' => 'Pré-embauche', 'date_debut' => '2025-03-01', 'date_fin' => '2025-08-31', 'valide' => false],
    ['id' => 3, 'etudiant_nom' => 'Voltaire François', 'entreprise_nom' => 'Global IT Services', 'type_stage' => 'Ouvrier', 'date_debut' => '2025-05-01', 'date_fin' => '2025-06-30', 'valide' => true],
    ['id' => 4, 'etudiant_nom' => 'Rousseau Sophie', 'entreprise_nom' => 'Data Insights Ltd', 'type_stage' => 'Fin d\'études', 'date_debut' => '2025-02-01', 'date_fin' => '2025-07-31', 'valide' => false],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Stages</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Stages</h2>
            <a href="/admin/gestion-acad/stages/create" class="btn btn-primary-blue">
                <span class="material-icons">add_circle</span>
                Enregistrer un Nouveau Stage
            </a>
        </div>

        <?php if (!empty($stages_enregistres)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Entreprise</th>
                    <th>Type de Stage</th>
                    <th>Période</th>
                    <th>Validé</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($stages_enregistres as $stage): ?>
                    <tr>
                        <td><?= e($stage['etudiant_nom']); ?></td>
                        <td><?= e($stage['entreprise_nom']); ?></td>
                        <td><?= e($stage['type_stage']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($stage['date_debut']))) . ' au ' . e(date('d/m/Y', strtotime($stage['date_fin']))); ?></td>
                        <td>
                                <span class="status-indicator <?= $stage['valide'] ? 'status-healthy' : 'status-inactive'; ?>">
                                    <?= $stage['valide'] ? 'Oui' : 'Non'; ?>
                                </span>
                        </td>
                        <td class="actions">
                            <?php if (!$stage['valide']): ?>
                                <form action="/admin/gestion-acad/stages/validate/<?= e($stage['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir valider ce stage ?');">
                                    <button type="submit" class="btn-action activate-btn" title="Valider le stage">
                                        <span class="material-icons">check_circle_outline</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="/admin/gestion-acad/stages/edit/<?= e($stage['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/gestion-acad/stages/delete/<?= e($stage['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce stage ? Cette action est irréversible.');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer">
                                    <span class="material-icons">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Aucun stage enregistré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/gestion-acad/stages/create" class="btn btn-primary-blue">Enregistrer le premier stage</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour liste_stages.php */
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

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        margin: 0;
    }

    /* Boutons - réutilisation des styles existants */
    .btn {
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
        text-decoration: none;
    }

    .btn-primary-blue {
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    /* Tableaux de données - réutilisation */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: var(--spacing-md);
        font-size: var(--font-size-base);
    }

    .data-table th,
    .data-table td {
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-light);
        text-align: left;
        color: var(--text-primary);
    }

    .data-table th {
        background-color: var(--bg-secondary);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
    }

    .data-table tbody tr:nth-child(even) {
        background-color: var(--primary-gray-light);
    }

    .data-table tbody tr:hover {
        background-color: var(--border-medium);
        transition: background-color var(--transition-fast);
    }

    .actions {
        text-align: center;
        white-space: nowrap;
    }

    .btn-action {
        background: none;
        border: none;
        cursor: pointer;
        padding: var(--spacing-xs);
        border-radius: var(--border-radius-sm);
        transition: background-color var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-size: var(--font-size-xl);
        text-decoration: none;
    }

    .btn-action:hover {
        background-color: var(--primary-gray-light);
    }

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .btn-action.activate-btn { color: var(--primary-green); } /* Pour le bouton Valider (check_circle_outline) */
    .btn-action.activate-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    /* Indicateurs de statut spécifiques */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 70px; /* Taille minimale pour uniformiser */
        text-align: center;
    }

    .status-healthy { /* Pour 'Validé' (Oui) */
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-inactive { /* Pour 'Non validé' (Non) */
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>