<?php
// src/Frontend/views/Administration/Habilitations/liste_groupes.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les groupes (proviennent du contrôleur HabilitationController)
// Ces données sont des exemples pour structurer la vue.
//

$groupes_enregistres = $data['groupes_enregistres'] ?? [
    ['id' => 1, 'code' => 'GRP_ADMIN', 'libelle' => 'Administrateur', 'description' => 'Accès complet au système.'],
    ['id' => 2, 'code' => 'GRP_RS', 'libelle' => 'Responsable Scolarité', 'description' => 'Gestion des inscriptions et notes.'],
    ['id' => 3, 'code' => 'GRP_AGENT_CONFORMITE', 'libelle' => 'Agent de Conformité', 'description' => 'Vérification des rapports.'],
    ['id' => 4, 'code' => 'GRP_COMMISSION', 'libelle' => 'Membre de Commission', 'description' => 'Évaluation des rapports de soutenance.'],
    ['id' => 5, 'code' => 'GRP_ETUDIANT', 'libelle' => 'Étudiant', 'description' => 'Accès à l\'espace personnel étudiant.'],
    ['id' => 6, 'code' => 'GRP_ENSEIGNANT', 'libelle' => 'Enseignant', 'description' => 'Accès aux outils pédagogiques et de suivi.'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Groupes d'Utilisateurs</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Groupes</h2>
            <a href="/admin/habilitations/groupes/create" class="btn btn-primary-blue">
                <span class="material-icons">add_circle</span>
                Ajouter un Groupe
            </a>
        </div>

        <?php if (!empty($groupes_enregistres)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($groupes_enregistres as $groupe): ?>
                    <tr>
                        <td><?= e($groupe['code']); ?></td>
                        <td><?= e($groupe['libelle']); ?></td>
                        <td><?= e(mb_strimwidth($groupe['description'], 0, 80, '...')); ?></td>
                        <td class="actions">
                            <a href="/admin/habilitations/rattachements?group_id=<?= e($groupe['id']); ?>" class="btn-action permissions-btn" title="Gérer les permissions">
                                <span class="material-icons">vpn_key</span>
                            </a>
                            <a href="/admin/habilitations/groupes/edit/<?= e($groupe['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/habilitations/groupes/delete/<?= e($groupe['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ? Cela affectera les utilisateurs qui y sont rattachés.');">
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
            <p class="text-center text-muted">Aucun groupe d'utilisateurs enregistré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/habilitations/groupes/create" class="btn btn-primary-blue">Ajouter le premier groupe</a>
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
    /* Styles spécifiques pour liste_groupes.php */
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

    .btn-action.permissions-btn { color: var(--accent-violet); } /* Couleur distincte pour gérer les permissions */
    .btn-action.permissions-btn:hover { background-color: rgba(139, 92, 246, 0.1); }

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }