<?php
// src/Frontend/views/PersonnelAdministratif/Conformite/liste_rapports_traites_conformite.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les rapports traités (proviennent du ConformiteController)
//
//

$rapports_traites = $data['rapports_traites'] ?? [
    ['id' => 1, 'numero_rapport' => 'RAP-2025-0040', 'titre' => 'Gestion de Bases de Données Avancées', 'etudiant_nom_complet' => 'Ahmed Benali', 'date_soumission' => '2025-06-25', 'decision_conformite' => 'Conforme', 'date_decision' => '2025-06-26 10:00:00'],
    ['id' => 2, 'numero_rapport' => 'RAP-2025-0041', 'titre' => 'Sécurité des Réseaux et Cryptographie', 'etudiant_nom_complet' => 'Clara Durand', 'date_soumission' => '2025-06-26', 'decision_conformite' => 'Non Conforme', 'date_decision' => '2025-06-27 14:30:00'],
    ['id' => 3, 'numero_rapport' => 'RAP-2025-0042', 'titre' => 'Systèmes Distribués et Cloud Computing', 'etudiant_nom_complet' => 'Lucas Moreau', 'date_soumission' => '2025-06-27', 'decision_conformite' => 'Conforme', 'date_decision' => '2025-06-28 09:00:00'],
];

// Options de filtrage (simulées)
$statuts_conformite_filtre = ['ALL' => 'Tous les statuts', 'Conforme' => 'Conforme', 'Non Conforme' => 'Non Conforme'];
$periodes_filtre = ['ALL' => 'Toutes les périodes', 'CURRENT_MONTH' => 'Mois actuel', 'LAST_30_DAYS' => '30 derniers jours'];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Rapports Traités (Conformité)</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Rapports Traités</h2>
        <form id="rapportTraiteFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_status">Statut de Conformité :</label>
                <select id="filter_status" name="status">
                    <?php foreach ($statuts_conformite_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['status'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_periode">Période de Décision :</label>
                <select id="filter_periode" name="periode">
                    <?php foreach ($periodes_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['periode'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_keyword">Recherche (Titre, Étudiant) :</label>
                <input type="text" id="filter_keyword" name="keyword" value="<?= e($_GET['keyword'] ?? ''); ?>" placeholder="Rechercher un rapport...">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/conformite/rapports-traites'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Historique des Rapports Vérifiés</h2>
        <?php if (!empty($rapports_traites)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Numéro Rapport</th>
                    <th>Titre du Rapport</th>
                    <th>Étudiant</th>
                    <th>Date Soumission</th>
                    <th>Décision Conformité</th>
                    <th>Date Décision</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rapports_traites as $rapport): ?>
                    <tr>
                        <td><?= e($rapport['numero_rapport']); ?></td>
                        <td><?= e(mb_strimwidth($rapport['titre'], 0, 60, '...')); ?></td>
                        <td><?= e($rapport['etudiant_nom_complet']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($rapport['date_soumission']))); ?></td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(str_replace(' ', '-', e($rapport['decision_conformite']))); ?>">
                                    <?= e($rapport['decision_conformite']); ?>
                                </span>
                        </td>
                        <td><?= e(date('d/m/Y H:i', strtotime($rapport['date_decision']))); ?></td>
                        <td class="actions">
                            <a href="/personnel/conformite/details-rapport/<?= e($rapport['id']); ?>" class="btn-action view-btn" title="Consulter la décision">
                                <span class="material-icons">visibility</span>
                            </a>
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
            <p class="text-center text-muted">Aucun rapport traité pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const rapportTraiteFilterForm = document.getElementById('rapportTraiteFilterForm');
        if (rapportTraiteFilterForm) {
            rapportTraiteFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(rapportTraiteFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/personnel/conformite/rapports-traites?${queryParams.toString()}`;
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
    /* Styles spécifiques pour liste_rapports_traites_conformite.php */
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

    .section-header { /* Réutilisé des listes d'administration */
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    .section-title { /* Réutilisé des formulaires admin */
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        margin: 0;
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

    .btn-action.view-btn { /* Bouton "Consulter la décision" */
        color: var(--primary-blue);
    }
    .btn-action.view-btn:hover {
        background-color: rgba(59, 130, 246, 0.1);
    }

    /* Statuts de conformité */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-conforme {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-non-conforme {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>