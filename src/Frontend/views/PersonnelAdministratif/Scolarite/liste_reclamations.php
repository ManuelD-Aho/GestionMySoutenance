<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/liste_reclamations_scolarite.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les réclamations (proviennent du ScolariteController)
//
//

$reclamations_scolarite = $data['reclamations_scolarite'] ?? [
    ['id' => 1, 'etudiant_nom' => 'Dupont Jean', 'objet' => 'Erreur de note en INFO101', 'date_soumission' => '2025-06-15', 'statut' => 'En cours', 'priorite' => 'Haute'],
    ['id' => 2, 'etudiant_nom' => 'Curie Marie', 'objet' => 'Problème d\'inscription 2024-2025', 'date_soumission' => '2025-06-10', 'statut' => 'Clôturée', 'priorite' => 'Moyenne'],
    ['id' => 3, 'etudiant_nom' => 'Voltaire François', 'objet' => 'Demande d\'attestation de scolarité', 'date_soumission' => '2025-06-18', 'statut' => 'Nouvelle', 'priorite' => 'Basse'],
    ['id' => 4, 'etudiant_nom' => 'Rousseau Sophie', 'objet' => 'Absence de bulletin de notes S1', 'date_soumission' => '2025-06-01', 'statut' => 'En cours', 'priorite' => 'Moyenne'],
];

// Options de filtrage
$statuts_reclamation_filtre = $data['statuts_reclamation_filtre'] ?? [
    'ALL' => 'Tous les statuts', 'Nouvelle' => 'Nouvelle', 'En cours' => 'En cours', 'En attente' => 'En attente', 'Clôturée' => 'Clôturée', 'Rejetée' => 'Rejetée'
];
$priorites_filtre = $data['priorites_filtre'] ?? [
    'ALL' => 'Toutes les priorités', 'Haute' => 'Haute', 'Moyenne' => 'Moyenne', 'Basse' => 'Basse'
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Gestion des Réclamations (Scolarité)</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Réclamations</h2>
        <form id="reclamationFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_statut">Statut :</label>
                <select id="filter_statut" name="statut">
                    <?php foreach ($statuts_reclamation_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['statut'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_priorite">Priorité :</label>
                <select id="filter_priorite" name="priorite">
                    <?php foreach ($priorites_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['priorite'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_date_debut">Date de début :</label>
                <input type="date" id="filter_date_debut" name="date_debut" value="<?= e($_GET['date_debut'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="filter_date_fin">Date de fin :</label>
                <input type="date" id="filter_date_fin" name="date_fin" value="<?= e($_GET['date_fin'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="filter_keyword">Recherche (Étudiant, Objet) :</label>
                <input type="text" id="filter_keyword" name="keyword" value="<?= e($_GET['keyword'] ?? ''); ?>" placeholder="Nom étudiant ou objet...">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/scolarite/liste-reclamations'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Liste des Réclamations</h2>
        <?php if (!empty($reclamations_scolarite)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Étudiant</th>
                    <th>Objet</th>
                    <th>Date Soumission</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reclamations_scolarite as $reclamation): ?>
                    <tr>
                        <td><?= e($reclamation['id']); ?></td>
                        <td><?= e($reclamation['etudiant_nom']); ?></td>
                        <td><?= e($reclamation['objet']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($reclamation['date_soumission']))); ?></td>
                        <td>
                                <span class="priority-indicator priority-<?= strtolower(e($reclamation['priorite'])); ?>">
                                    <?= e($reclamation['priorite']); ?>
                                </span>
                        </td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(e(str_replace(' ', '-', $reclamation['statut']))); ?>">
                                    <?= e($reclamation['statut']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/personnel/scolarite/reclamation/view/<?= e($reclamation['id']); ?>" class="btn-action view-btn" title="Voir les détails">
                                <span class="material-icons">visibility</span>
                            </a>
                            <a href="/personnel/scolarite/reclamation/edit/<?= e($reclamation['id']); ?>" class="btn-action edit-btn" title="Modifier le statut">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/personnel/scolarite/reclamation/delete/<?= e($reclamation['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Supprimer cette réclamation ?');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer la réclamation">
                                    <span class="material-icons">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination-controls mt-lg text-center">
                <button class="btn btn-secondary-gray" disabled>Précédent</button>
                <span class="current-page">Page 1 de X</span>
                <button class="btn btn-secondary-gray">Suivant</button>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune réclamation trouvée pour les critères sélectionnés.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const reclamationFilterForm = document.getElementById('reclamationFilterForm');
        if (reclamationFilterForm) {
            reclamationFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(reclamationFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/personnel/scolarite/liste-reclamations?${queryParams.toString()}`;
            });
        }

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour liste_reclamations_scolarite.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1200px;
        margin: var(--spacing-xl) auto;
    }

    .dashboard-title {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
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

    /* Filtres - réutilisés et adaptés */
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
        align-items: flex-end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group input[type="text"],
    .form-group input[type="date"],
    .form-group select {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    /* Boutons de filtre */
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

    .btn-secondary-gray {
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }

    .btn-secondary-gray:hover {
        background-color: var(--border-medium);
        box-shadow: var(--shadow-sm);
    }


    /* Tableaux de données - réutilisés */
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

    .btn-action.view-btn { color: var(--primary-blue); }
    .btn-action.view-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.edit-btn { color: var(--primary-green); }
    .btn-action.edit-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }


    /* Indicateurs de statut et de priorité */
    .status-indicator, .priority-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 80px;
        text-align: center;
    }

    /* Statuts de réclamation */
    .status-nouvelle {
        background-color: var(--primary-violet-light);
        color: var(--primary-violet-dark);
    }
    .status-en-cours {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }
    .status-en-attente {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }
    .status-clôturée {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }
    .status-rejetée {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    /* Priorités */
    .priority-basse {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }
    .priority-moyenne {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }
    .priority-haute {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>