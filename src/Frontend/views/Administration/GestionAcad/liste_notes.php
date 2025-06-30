<?php
// src/Frontend/views/Administration/GestionAcad/liste_notes.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les notes (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//

$notes_enregistrees = $data['notes_enregistrees'] ?? [
    ['id' => 1, 'etudiant_nom' => 'Dupont Jean', 'ecue_libelle' => 'INFO101 - Prog. Avancée', 'note_valeur' => 15.50, 'date_saisie' => '2025-06-20'],
    ['id' => 2, 'etudiant_nom' => 'Curie Marie', 'ecue_libelle' => 'MATH101 - Algèbre', 'note_valeur' => 12.00, 'date_saisie' => '2025-06-21'],
    ['id' => 3, 'etudiant_nom' => 'Voltaire François', 'ecue_libelle' => 'PROJ201 - Gestion de Projet', 'note_valeur' => 18.75, 'date_saisie' => '2025-06-22'],
    ['id' => 4, 'etudiant_nom' => 'Dupont Jean', 'ecue_libelle' => 'PROJ201 - Gestion de Projet', 'note_valeur' => 10.00, 'date_saisie' => '2025-06-22'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Notes</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Notes</h2>
            <a href="/admin/gestion-acad/notes/create" class="btn btn-primary-blue">
                <span class="material-icons">add_circle</span>
                Saisir une Nouvelle Note
            </a>
        </div>

        <?php if (!empty($notes_enregistrees)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>ECUE</th>
                    <th>Note</th>
                    <th>Date de Saisie</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($notes_enregistrees as $note): ?>
                    <tr>
                        <td><?= e($note['etudiant_nom']); ?></td>
                        <td><?= e($note['ecue_libelle']); ?></td>
                        <td>
                                <span class="note-value <?= $note['note_valeur'] < 10 ? 'note-fail' : ($note['note_valeur'] >= 15 ? 'note-excellent' : 'note-pass'); ?>">
                                    <?= e(number_format($note['note_valeur'], 2, ',', '')); ?> / 20
                                </span>
                        </td>
                        <td><?= e(date('d/m/Y', strtotime($note['date_saisie']))); ?></td>
                        <td class="actions">
                            <a href="/admin/gestion-acad/notes/edit/<?= e($note['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/gestion-acad/notes/delete/<?= e($note['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette note ? Cette action est irréversible.');">
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
            <p class="text-center text-muted">Aucune note enregistrée pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/gestion-acad/notes/create" class="btn btn-primary-blue">Saisir la première note</a>
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
    /* Styles spécifiques pour liste_notes.php */
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

    /* Styles spécifiques pour les notes */
    .note-value {
        font-weight: var(--font-weight-bold);
        padding: 0.2em 0.5em;
        border-radius: var(--border-radius-sm);
    }

    .note-fail {
        color: var(--accent-red-dark);
        background-color: var(--accent-red-light);
    }

    .note-pass {
        color: var(--primary-blue-dark);
        background-color: var(--primary-blue-light);
    }

    .note-excellent {
        color: var(--primary-green-dark);
        background-color: var(--primary-green-light);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>