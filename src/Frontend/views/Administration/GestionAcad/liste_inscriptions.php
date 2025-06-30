<?php
// src/Frontend/views/Administration/GestionAcad/liste_inscriptions.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les inscriptions (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//

$inscriptions_enregistrees = $data['inscriptions_enregistrees'] ?? [
    ['id' => 1, 'etudiant_nom' => 'Dupont Jean', 'annee_academique' => '2024-2025', 'niveau_etude' => 'Master 2', 'frais_inscription' => 500000.00, 'statut_paiement' => 'Payé'],
    ['id' => 2, 'etudiant_nom' => 'Curie Marie', 'annee_academique' => '2024-2025', 'niveau_etude' => 'Master 2', 'frais_inscription' => 500000.00, 'statut_paiement' => 'En attente'],
    ['id' => 3, 'etudiant_nom' => 'Voltaire François', 'annee_academique' => '2024-2025', 'niveau_etude' => 'Licence 3', 'frais_inscription' => 300000.00, 'statut_paiement' => 'Partiel'],
    ['id' => 4, 'etudiant_nom' => 'Rousseau Sophie', 'annee_academique' => '2023-2024', 'niveau_etude' => 'Master 1', 'frais_inscription' => 450000.00, 'statut_paiement' => 'Payé'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Inscriptions</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Inscriptions</h2>
            <a href="/admin/gestion-acad/inscriptions/create" class="btn btn-primary-blue">
                <span class="material-icons">add_circle</span>
                Ajouter une Inscription
            </a>
        </div>

        <?php if (!empty($inscriptions_enregistrees)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Année Académique</th>
                    <th>Niveau d'Étude</th>
                    <th>Frais d'Inscription</th>
                    <th>Statut de Paiement</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($inscriptions_enregistrees as $inscription): ?>
                    <tr>
                        <td><?= e($inscription['etudiant_nom']); ?></td>
                        <td><?= e($inscription['annee_academique']); ?></td>
                        <td><?= e($inscription['niveau_etude']); ?></td>
                        <td><?= e(number_format($inscription['frais_inscription'], 2, ',', ' ')); ?> FCFA</td>
                        <td>
                                <span class="status-indicator
                                    <?php
                                if ($inscription['statut_paiement'] === 'Payé') echo 'status-healthy';
                                elseif ($inscription['statut_paiement'] === 'En attente') echo 'status-inactive';
                                elseif ($inscription['statut_paiement'] === 'Partiel') echo 'status-partial';
                                elseif ($inscription['statut_paiement'] === 'En retard') echo 'status-error';
                                else echo 'status-unknown';
                                ?>
                                ">
                                    <?= e($inscription['statut_paiement']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/admin/gestion-acad/inscriptions/edit/<?= e($inscription['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/gestion-acad/inscriptions/delete/<?= e($inscription['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?');">
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
            <p class="text-center text-muted">Aucune inscription enregistrée pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/gestion-acad/inscriptions/create" class="btn btn-primary-blue">Enregistrer la première inscription</a>
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
    /* Styles spécifiques pour liste_inscriptions.php */
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

    /* Indicateurs de statut spécifiques */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 90px; /* Taille minimale pour uniformiser */
        text-align: center;
    }

    .status-healthy { /* Pour 'Payé' */
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-inactive { /* Pour 'En attente' */
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }

    .status-partial { /* Pour 'Partiel' */
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-error { /* Pour 'En retard' */
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .status-unknown { /* Fallback */
        background-color: var(--primary-gray-light);
        color: var(--text-secondary);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>