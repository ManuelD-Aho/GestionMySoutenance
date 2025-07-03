<?php
// src/Frontend/views/Administration/Habilitations/liste_traitements.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les traitements (proviennent du contrôleur HabilitationController)
// Ces données sont des exemples pour structurer la vue.
//

$traitements_enregistres = $data['traitements_enregistres'] ?? [
    ['id' => 101, 'code' => 'TRAIT_ANNEE_CREATE', 'libelle' => 'Créer Année Académique', 'description' => 'Permet de créer une nouvelle année académique dans le système.'],
    ['id' => 102, 'code' => 'TRAIT_ANNEE_UPDATE', 'libelle' => 'Modifier Année Académique', 'description' => 'Permet de modifier les détails d\'une année académique existante.'],
    ['id' => 103, 'code' => 'TRAIT_MODELE_DOC_MANAGE', 'libelle' => 'Gérer Modèles Documents', 'description' => 'Accès complet à la gestion des modèles de documents et notifications.'],
    ['id' => 104, 'code' => 'TRAIT_RAPPORT_SOUMETTRE', 'libelle' => 'Soumettre Rapport', 'description' => 'Permet à l\'étudiant de soumettre son rapport de soutenance.'],
    ['id' => 105, 'code' => 'TRAIT_RAPPORT_CONFORMITE_VERIFY', 'libelle' => 'Vérifier Conformité Rapport', 'description' => 'Permet de vérifier la conformité administrative d\'un rapport.'],
    ['id' => 106, 'code' => 'TRAIT_COMMISSION_VOTER', 'libelle' => 'Voter sur Rapport', 'description' => 'Permet aux membres de commission de voter sur la validation d\'un rapport.'],
    ['id' => 107, 'code' => 'TRAIT_PV_REDIGER', 'libelle' => 'Rédiger PV', 'description' => 'Permet de rédiger le procès-verbal d\'une session de soutenance.'],
    ['id' => 108, 'code' => 'TRAIT_PV_VALIDER', 'libelle' => 'Valider PV', 'description' => 'Permet de valider formellement un procès-verbal.'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Traitements (Permissions)</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Traitements</h2>
            <a href="/admin/habilitations/traitements/create" class="btn btn-primary-blue">
                <span class="material-icons">add_circle</span>
                Ajouter un Traitement
            </a>
        </div>

        <?php if (!empty($traitements_enregistres)): ?>
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
                <?php foreach ($traitements_enregistres as $traitement): ?>
                    <tr>
                        <td><?= e($traitement['code']); ?></td>
                        <td><?= e($traitement['libelle']); ?></td>
                        <td><?= e(mb_strimwidth($traitement['description'], 0, 80, '...')); ?></td>
                        <td class="actions">
                            <a href="/admin/habilitations/traitements/edit/<?= e($traitement['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/habilitations/traitements/delete/<?= e($traitement['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce traitement ? Cela affectera les groupes qui y sont rattachés.');">
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
            <p class="text-center text-muted">Aucun traitement enregistré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/habilitations/traitements/create" class="btn btn-primary-blue">Ajouter le premier traitement</a>
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
    /* Styles spécifiques pour liste_traitements.php */
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