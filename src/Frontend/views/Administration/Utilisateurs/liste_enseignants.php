<?php
// src/Frontend/views/Administration/Utilisateurs/liste_enseignants.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les enseignants (proviennent du contrôleur UtilisateurController)
// Ces données sont des exemples pour structurer la vue.
//

$enseignants_enregistres = $data['enseignants_enregistres'] ?? [
    ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean.dupont@univ.com', 'matricule_enseignant' => 'ENS-001', 'departement' => 'Informatique', 'statut' => 'Permanent'],
    ['id' => 2, 'nom' => 'Martin', 'prenom' => 'Sophie', 'email' => 'sophie.martin@univ.com', 'matricule_enseignant' => 'ENS-002', 'departement' => 'Mathématiques', 'statut' => 'Permanent'],
    ['id' => 3, 'nom' => 'Lefevre', 'prenom' => 'Paul', 'email' => 'paul.lefevre@univ.com', 'matricule_enseignant' => 'ENS-003', 'departement' => 'Physique', 'statut' => 'Contractuel'],
    ['id' => 4, 'nom' => 'Bernard', 'prenom' => 'Claire', 'email' => 'claire.bernard@univ.com', 'matricule_enseignant' => 'ENS-004', 'departement' => 'Informatique', 'statut' => 'Vacataire'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Enseignants</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Enseignants</h2>
            <a href="/admin/utilisateurs/enseignant/create" class="btn btn-primary-blue">
                <span class="material-icons">person_add</span>
                Ajouter un Enseignant
            </a>
        </div>

        <?php if (!empty($enseignants_enregistres)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Matricule</th>
                    <th>Département</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($enseignants_enregistres as $enseignant): ?>
                    <tr>
                        <td><?= e($enseignant['prenom']) . ' ' . e($enseignant['nom']); ?></td>
                        <td><?= e($enseignant['email']); ?></td>
                        <td><?= e($enseignant['matricule_enseignant']); ?></td>
                        <td><?= e($enseignant['departement']); ?></td>
                        <td><span class="status-indicator status-<?= strtolower(e($enseignant['statut'])); ?>"><?= e($enseignant['statut']); ?></span></td>
                        <td class="actions">
                            <a href="/admin/gestion-acad/manage-enseignant-carrieres?enseignant_id=<?= e($enseignant['id']); ?>" class="btn-action careers-btn" title="Gérer la carrière (grades/fonctions)">
                                <span class="material-icons">history_edu</span>
                            </a>
                            <a href="/admin/utilisateurs/enseignant/edit/<?= e($enseignant['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/utilisateurs/enseignant/delete/<?= e($enseignant['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?');">
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
            <p class="text-center text-muted">Aucun enseignant enregistré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/utilisateurs/enseignant/create" class="btn btn-primary-blue">Ajouter le premier enseignant</a>
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
    /* Styles spécifiques pour liste_enseignants.php */
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

    .btn-action.careers-btn { color: var(--accent-violet); } /* Icône pour gérer carrière */
    .btn-action.careers-btn:hover { background-color: rgba(139, 92, 246, 0.1); }

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    /* Statuts d'enseignant */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }

    .status-permanent {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-contractuel {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .status-vacataire {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>