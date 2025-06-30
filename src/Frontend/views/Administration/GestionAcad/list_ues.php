<?php
// src/Frontend/views/Administration/GestionAcad/list_ues.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les UEs (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//

$ues_enregistrees = $data['ues_enregistrees'] ?? [
    ['id' => 1, 'code_ue' => 'UEINFO01', 'libelle' => 'Informatique Fondamentale', 'description' => 'Introduction aux concepts informatiques de base.'],
    ['id' => 2, 'code_ue' => 'UEDEV02', 'libelle' => 'Développement Logiciel Avancé', 'description' => 'Approfondissement des techniques de développement.'],
    ['id' => 3, 'code_ue' => 'UERES03', 'libelle' => 'Réseaux et Cybersécurité', 'description' => 'Concepts avancés de réseaux et sécurité informatique.'],
    ['id' => 4, 'code_ue' => 'UEPROJ04', 'libelle' => 'Management de Projet IT', 'description' => 'Méthodologies de gestion de projet en TI.'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Unités d'Enseignement (UE)</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des UEs Enregistrées</h2>
            <a href="/admin/gestion-acad/ues/create" class="btn btn-primary-blue">
                <span class="material-icons">add_circle</span>
                Ajouter une UE
            </a>
        </div>

        <?php if (!empty($ues_enregistrees)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Code UE</th>
                    <th>Libellé UE</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ues_enregistrees as $ue): ?>
                    <tr>
                        <td><?= e($ue['code_ue']); ?></td>
                        <td><?= e($ue['libelle']); ?></td>
                        <td><?= e(mb_strimwidth($ue['description'], 0, 80, '...')); // Tronquer la description si longue ?></td>
                        <td class="actions">
                            <a href="/admin/gestion-acad/ues/edit/<?= e($ue['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/gestion-acad/ues/delete/<?= e($ue['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette UE ? Cette action est irréversible et pourrait affecter les ECUEs associés.');">
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
            <p class="text-center text-muted">Aucune UE n'est enregistrée dans le système pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/gestion-acad/ues/create" class="btn btn-primary-blue">Ajouter la première UE</a>
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
            // Vous pouvez ajouter une logique d'affichage de toast ou alerte ici
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour list_ues.php */
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

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>