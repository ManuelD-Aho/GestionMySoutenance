<?php
// src/Frontend/views/PersonnelAdministratif/Conformite/liste_rapports_a_verifier.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les rapports (proviennent du ConformiteController)
//
//

$rapports_a_verifier = $data['rapports_a_verifier'] ?? [
    ['id' => 1, 'numero_rapport' => 'RAP-2025-0049', 'titre' => 'Développement d\'une application de gestion académique', 'etudiant_nom_complet' => 'Marie Dubois', 'date_soumission' => '2025-06-30 10:00:00'],
    ['id' => 2, 'numero_rapport' => 'RAP-2025-0050', 'titre' => 'Analyse des Big Data dans le secteur de la santé', 'etudiant_nom_complet' => 'Pierre Lambert', 'date_soumission' => '2025-06-30 11:30:00'],
    ['id' => 3, 'numero_rapport' => 'RAP-2025-0051', 'titre' => 'Cybersécurité des systèmes embarqués', 'etudiant_nom_complet' => 'Léa François', 'date_soumission' => '2025-06-30 14:00:00'],
    ['id' => 4, 'numero_rapport' => 'RAP-2025-0052', 'titre' => 'Impact de l\'IA sur le marché du travail', 'etudiant_nom_complet' => 'Ahmed Benali', 'date_soumission' => '2025-07-01 09:00:00'],
];

// Options de filtrage (simulées)
$periodes_filtre = ['ALL' => 'Toutes les périodes', 'CURRENT_MONTH' => 'Mois actuel', 'LAST_7_DAYS' => '7 derniers jours'];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Rapports à Vérifier (Conformité)</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Rapports</h2>
        <form id="rapportFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_periode">Période de Soumission :</label>
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
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/conformite/rapports-a-verifier'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Rapports en Attente de Vérification</h2>
        <?php if (!empty($rapports_a_verifier)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Numéro Rapport</th>
                    <th>Titre du Rapport</th>
                    <th>Étudiant</th>
                    <th>Date Soumission</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rapports_a_verifier as $rapport): ?>
                    <tr>
                        <td><?= e($rapport['numero_rapport']); ?></td>
                        <td><?= e(mb_strimwidth($rapport['titre'], 0, 70, '...')); ?></td>
                        <td><?= e($rapport['etudiant_nom_complet']); ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($rapport['date_soumission']))); ?></td>
                        <td class="actions">
                            <a href="/personnel/conformite/details-rapport/<?= e($rapport['id']); ?>" class="btn-action check-btn" title="Consulter et Vérifier">
                                <span class="material-icons">check_circle_outline</span>
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
            <p class="text-center text-muted">Aucun rapport en attente de vérification de conformité.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const rapportFilterForm = document.getElementById('rapportFilterForm');
        if (rapportFilterForm) {
            rapportFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(rapportFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/personnel/conformite/rapports-a-verifier?${queryParams.toString()}`;
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
    /* Styles spécifiques pour liste_rapports_a_verifier.php */
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

    .btn-action.check-btn { /* Bouton "Consulter et Vérifier" */
        color: var(--primary-green);
    }
    .btn-action.check-btn:hover {
        background-color: rgba(16, 185, 129, 0.1);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>