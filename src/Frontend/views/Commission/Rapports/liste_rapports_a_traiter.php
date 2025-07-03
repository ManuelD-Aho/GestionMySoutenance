<?php
// src/Frontend/views/Commission/Rapports/liste_rapports_a_traiter.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les rapports (proviennent du contrôleur ValidationRapportController ou CommissionController)
// Ces données sont des exemples pour structurer la vue.
//
//

$rapports_a_traiter = $data['rapports_a_traiter'] ?? [
    ['id' => 1, 'numero_rapport' => 'RAP-2025-0045', 'titre' => 'Optimisation des Processus Logistiques par IA', 'etudiant_nom_complet' => 'Dupont Jean', 'date_soumission' => '2025-06-28', 'statut_commission' => 'En attente d\'évaluation', 'session_id' => 1, 'session_libelle' => 'Session Juin Vague 1'],
    ['id' => 2, 'numero_rapport' => 'RAP-2025-0046', 'titre' => 'Analyse de Données Financières', 'etudiant_nom_complet' => 'Curie Marie', 'date_soumission' => '2025-06-29', 'statut_commission' => 'En attente d\'évaluation', 'session_id' => 1, 'session_libelle' => 'Session Juin Vague 1'],
    ['id' => 3, 'numero_rapport' => 'RAP-2025-0047', 'titre' => 'Sécurité des Applications Web', 'etudiant_nom_complet' => 'Voltaire François', 'date_soumission' => '2025-06-30', 'statut_commission' => 'En cours de vote', 'session_id' => 1, 'session_libelle' => 'Session Juin Vague 1'],
    ['id' => 4, 'numero_rapport' => 'RAP-2025-0048', 'titre' => 'Développement Mobile Cross-Platform', 'etudiant_nom_complet' => 'Rousseau Sophie', 'date_soumission' => '2025-07-01', 'statut_commission' => 'En attente d\'évaluation', 'session_id' => 2, 'session_libelle' => 'Session Juillet Vague 1'],
];

// Options de filtrage
$sessions_disponibles_filtre = $data['sessions_disponibles_filtre'] ?? [
    ['id' => 'ALL', 'libelle' => 'Toutes les sessions'],
    ['id' => 1, 'libelle' => 'Session Juin Vague 1'],
    ['id' => 2, 'libelle' => 'Session Juillet Vague 1'],
];

$statuts_commission_filtre = $data['statuts_commission_filtre'] ?? [
    'ALL' => 'Tous les statuts',
    'En attente d\'évaluation' => 'En attente d\'évaluation',
    'En cours de vote' => 'En cours de vote',
    'Rejeté par Conformité' => 'Rejeté par Conformité', // Si un rapport est renvoyé à la commission après non conformité
    // D'autres statuts comme 'Validé' ou 'Refusé' pourraient être inclus pour un historique des rapports traités
];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Rapports à Traiter par la Commission</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Rapports</h2>
        <form id="rapportFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_session">Session :</label>
                <select id="filter_session" name="session_id">
                    <?php foreach ($sessions_disponibles_filtre as $session): ?>
                        <option value="<?= e($session['id']); ?>" <?= (($_GET['session_id'] ?? 'ALL') == $session['id']) ? 'selected' : ''; ?>>
                            <?= e($session['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_status">Statut d'Évaluation :</label>
                <select id="filter_status" name="statut">
                    <?php foreach ($statuts_commission_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['statut'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
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
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/commission/rapports/liste'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Rapports en Attente d'Évaluation</h2>
        <?php if (!empty($rapports_a_traiter)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Numéro Rapport</th>
                    <th>Titre du Rapport</th>
                    <th>Étudiant</th>
                    <th>Date Soumission</th>
                    <th>Statut Commission</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rapports_a_traiter as $rapport): ?>
                    <tr>
                        <td><?= e($rapport['numero_rapport']); ?></td>
                        <td><?= e(mb_strimwidth($rapport['titre'], 0, 70, '...')); ?></td>
                        <td><?= e($rapport['etudiant_nom_complet']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($rapport['date_soumission']))); ?></td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(str_replace(' ', '-', e($rapport['statut_commission']))); ?>">
                                    <?= e($rapport['statut_commission']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/commission/rapports/details/<?= e($rapport['id']); ?>" class="btn-action view-btn" title="Consulter le rapport">
                                <span class="material-icons">visibility</span>
                            </a>
                            <a href="/commission/rapports/vote/<?= e($rapport['id']); ?>" class="btn-action evaluate-btn" title="Évaluer / Voter">
                                <span class="material-icons">how_to_vote</span>
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
            <p class="text-center text-muted">Aucun rapport en attente de traitement par la commission.</p>
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
                window.location.href = `/commission/rapports/liste?${queryParams.toString()}`;
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
    /* Styles spécifiques pour liste_rapports_a_traiter.php */
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

    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .section-title {
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

    /* Boutons - réutilisation */
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

    .btn-action.view-btn { color: var(--primary-blue); }
    .btn-action.view-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.evaluate-btn { color: var(--primary-green); }
    .btn-action.evaluate-btn:hover { background-color: rgba(16, 185, 129, 0.1); }


    /* Statuts spécifiques des rapports */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-en-attente-d-évaluation {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-en-cours-de-vote {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .status-rejeté-par-conformité {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }

    /* Pagination */
    .pagination-controls button {
        margin: 0 var(--spacing-xs);
    }
    .pagination-controls .current-page {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }
</style>