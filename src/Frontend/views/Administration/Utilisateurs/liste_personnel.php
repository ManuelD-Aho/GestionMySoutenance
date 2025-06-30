<?php
// src/Frontend/views/Administration/Utilisateurs/liste_personnel.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le personnel (proviennent du contrôleur UtilisateurController)
// Ces données sont des exemples pour structurer la vue.
//

$personnel_enregistres = $data['personnel_enregistres'] ?? [
    ['id' => 1, 'nom' => 'Durand', 'prenom' => 'Claire', 'email' => 'claire.durand@admin.com', 'code_personnel' => 'ADM-001', 'departement' => 'Scolarité', 'fonction' => 'Responsable Scolarité'],
    ['id' => 2, 'nom' => 'Petit', 'prenom' => 'Marc', 'email' => 'marc.petit@admin.com', 'code_personnel' => 'AC-001', 'departement' => 'Administration Générale', 'fonction' => 'Agent de Contrôle de Conformité'],
    ['id' => 3, 'nom' => 'Leroy', 'prenom' => 'Julie', 'email' => 'julie.leroy@admin.com', 'code_personnel' => 'SEC-001', 'departement' => 'Scolarité', 'fonction' => 'Secrétaire Administrative'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion du Personnel Administratif</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste du Personnel Administratif</h2>
            <a href="/admin/utilisateurs/personnel/create" class="btn btn-primary-blue">
                <span class="material-icons">person_add</span>
                Ajouter un Membre du Personnel
            </a>
        </div>

        <?php if (!empty($personnel_enregistres)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Code Personnel</th>
                    <th>Département</th>
                    <th>Fonction</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($personnel_enregistres as $personnel): ?>
                    <tr>
                        <td><?= e($personnel['prenom']) . ' ' . e($personnel['nom']); ?></td>
                        <td><?= e($personnel['email']); ?></td>
                        <td><?= e($personnel['code_personnel']); ?></td>
                        <td><?= e($personnel['departement']); ?></td>
                        <td><span class="status-indicator status-info"><?= e($personnel['fonction']); ?></span></td>
                        <td class="actions">
                            <a href="/admin/utilisateurs/personnel/edit/<?= e($personnel['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/utilisateurs/personnel/delete/<?= e($personnel['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce membre du personnel ?');">
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
            <p class="text-center text-muted">Aucun membre du personnel administratif enregistré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/utilisateurs/personnel/create" class="btn btn-primary-blue">Ajouter le premier membre du personnel</a>
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
    /* Styles spécifiques pour liste_personnel.php */
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

    /* Statuts de personnel */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 120px; /* Plus large pour les fonctions */
        text-align: center;
    }

    .status-info { /* Pour les fonctions, souvent plus d'informations */
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>