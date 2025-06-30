<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/gestion_inscriptions_scolarite.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les inscriptions (proviennent du ScolariteController)
//
//

$inscriptions_enregistrees = $data['inscriptions_enregistrees'] ?? [
    ['id' => 1, 'etudiant_nom' => 'Dupont Jean', 'annee_academique' => '2024-2025', 'niveau_etude' => 'Master 2', 'frais_inscription' => 500000.00, 'statut_paiement' => 'Payé'],
    ['id' => 2, 'etudiant_nom' => 'Curie Marie', 'annee_academique' => '2024-2025', 'niveau_etude' => 'Master 2', 'frais_inscription' => 500000.00, 'statut_paiement' => 'En attente'],
    ['id' => 3, 'etudiant_nom' => 'Voltaire François', 'annee_academique' => '2024-2025', 'niveau_etude' => 'Licence 3', 'frais_inscription' => 300000.00, 'statut_paiement' => 'Partiel'],
    ['id' => 4, 'etudiant_nom' => 'Rousseau Sophie', 'annee_academique' => '2023-2024', 'niveau_etude' => 'Master 1', 'frais_inscription' => 450000.00, 'statut_paiement' => 'Payé'],
];

// Options de filtrage
$annees_academiques_filtre = $data['annees_academiques_filtre'] ?? [
    'ALL' => 'Toutes les années', '2024-2025' => '2024-2025', '2023-2024' => '2023-2024'
];
$niveaux_etude_filtre = $data['niveaux_etude_filtre'] ?? [
    'ALL' => 'Tous les niveaux', 'Licence 3' => 'Licence 3', 'Master 1' => 'Master 1', 'Master 2' => 'Master 2'
];
$statuts_paiement_filtre = $data['statuts_paiement_filtre'] ?? [
    'ALL' => 'Tous les statuts', 'Payé' => 'Payé', 'En attente' => 'En attente', 'Partiel' => 'Partiel', 'En retard' => 'En retard'
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Gestion des Inscriptions (Scolarité)</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Inscriptions</h2>
        <form id="inscriptionFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_annee_acad">Année Académique :</label>
                <select id="filter_annee_acad" name="annee_academique">
                    <?php foreach ($annees_academiques_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['annee_academique'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_niveau">Niveau d'Étude :</label>
                <select id="filter_niveau" name="niveau_etude">
                    <?php foreach ($niveaux_etude_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['niveau_etude'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_statut_paiement">Statut Paiement :</label>
                <select id="filter_statut_paiement" name="statut_paiement">
                    <?php foreach ($statuts_paiement_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['statut_paiement'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_keyword">Recherche (Étudiant) :</label>
                <input type="text" id="filter_keyword" name="keyword" value="<?= e($_GET['keyword'] ?? ''); ?>" placeholder="Nom ou matricule...">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/scolarite/gestion-inscriptions'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Liste des Inscriptions</h2>
        <div class="action-buttons">
            <a href="/admin/gestion-acad/inscriptions/create" class="btn btn-primary-green">
                <span class="material-icons">add_box</span> Nouvelle Inscription
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
                                if ($inscription['statut_paiement'] === 'Payé') echo 'status-paid';
                                elseif ($inscription['statut_paiement'] === 'En attente') echo 'status-pending';
                                elseif ($inscription['statut_paiement'] === 'Partiel') echo 'status-partial';
                                elseif ($inscription['statut_paiement'] === 'En retard') echo 'status-overdue';
                                else echo 'status-unknown';
                                ?>
                                ">
                                    <?= e($inscription['statut_paiement']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/admin/gestion-acad/inscriptions/edit/<?= e($inscription['id']); ?>" class="btn-action edit-btn" title="Modifier l'inscription">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/gestion-acad/inscriptions/delete/<?= e($inscription['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Supprimer cette inscription ?');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer l'inscription">
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
            <p class="text-center text-muted">Aucune inscription enregistrée pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/gestion-acad/inscriptions/create" class="btn btn-primary-blue">Enregistrer la première inscription</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const inscriptionFilterForm = document.getElementById('inscriptionFilterForm');
        if (inscriptionFilterForm) {
            inscriptionFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(inscriptionFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/personnel/scolarite/gestion-inscriptions?${queryParams.toString()}`;
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
    /* Styles spécifiques pour gestion_inscriptions_scolarite.php */
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

    .btn-primary-green { /* Pour le bouton "Nouvelle Inscription" */
        color: var(--text-white);
        background-color: var(--primary-green);
    }
    .btn-primary-green:hover {
        background-color: var(--primary-green-dark);
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

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    /* Statuts de paiement (réutilisés) */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }

    .status-paid { /* Payé */
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-pending { /* En attente */
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-partial { /* Partiel */
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .status-overdue { /* En retard */
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .status-unknown { /* Fallback */
        background-color: var(--primary-gray-light);
        color: var(--text-secondary);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>