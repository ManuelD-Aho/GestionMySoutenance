<?php
// src/Frontend/views/Commission/historique_commission.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour l'historique (proviennent du contrôleur CommissionDashboardController ou un HistoriqueCommissionController dédié)
//

$sessions_historiques = $data['sessions_historiques'] ?? [
    ['id' => 100, 'libelle' => 'Session Juin 2025 - Vague 1', 'date_debut' => '2025-06-25', 'date_fin' => '2025-07-05', 'statut' => 'Clôturée', 'type' => 'Hybride'],
    ['id' => 99, 'libelle' => 'Session Mai 2025 - Rattrapages', 'date_debut' => '2025-05-10', 'date_fin' => '2025-05-15', 'statut' => 'Clôturée', 'type' => 'En Ligne (Asynchrone)'],
];

$rapports_traites_historique = $data['rapports_traites_historique'] ?? [
    ['id' => 1, 'numero_rapport' => 'RAP-2025-0045', 'titre' => 'Optimisation Logistique par IA', 'etudiant_nom' => 'Dupont Jean', 'date_validation' => '2025-07-05', 'decision_finale' => 'Approuvé en l\'état', 'note_finale' => 16.5, 'pv_id' => 10],
    ['id' => 2, 'numero_rapport' => 'RAP-2025-0046', 'titre' => 'Analyse de Données Financières', 'etudiant_nom' => 'Curie Marie', 'date_validation' => '2025-07-05', 'decision_finale' => 'Approuvé sous réserve', 'note_finale' => 14.0, 'pv_id' => 11],
    ['id' => 3, 'numero_rapport' => 'RAP-2025-0047', 'titre' => 'Sécurité des Applications Web', 'etudiant_nom' => 'Voltaire François', 'date_validation' => '2025-05-15', 'decision_finale' => 'Refusé', 'note_finale' => 8.0, 'pv_id' => 12],
];

// Options de filtrage
$periodes_filtre = ['ALL' => 'Toutes les périodes', '2025' => 'Année 2025', '2024' => 'Année 2024'];
$statuts_rapport_filtre = ['ALL' => 'Tous les statuts', 'Approuvé en l\'état' => 'Approuvé en l\'état', 'Approuvé sous réserve' => 'Approuvé sous réserve', 'Refusé' => 'Refusé'];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Historique de la Commission</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer l'Historique</h2>
        <form id="historyFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_periode">Période :</label>
                <select id="filter_periode" name="periode">
                    <?php foreach ($periodes_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['periode'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_statut_rapport">Statut du Rapport :</label>
                <select id="filter_statut_rapport" name="statut_rapport">
                    <?php foreach ($statuts_rapport_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['statut_rapport'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
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
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/commission/historique'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-sessions-historiques admin-card mt-xl">
        <h2 class="section-title">Sessions de Validation Clôturées</h2>
        <?php if (!empty($sessions_historiques)): ?>
            <div class="sessions-list-grid">
                <?php foreach ($sessions_historiques as $session): ?>
                    <div class="session-card session-status-<?= e(strtolower($session['statut'])); ?>">
                        <div class="card-header">
                            <h3 class="card-title"><?= e($session['libelle']); ?></h3>
                            <span class="status-indicator status-<?= e(strtolower($session['statut'])); ?>"><?= e($session['statut']); ?></span>
                        </div>
                        <div class="card-body">
                            <p><strong>Période :</strong> <?= e(date('d/m/Y', strtotime($session['date_debut']))) ?> - <?= e(date('d/m/Y', strtotime($session['date_fin']))) ?></p>
                            <p><strong>Type :</strong> <?= e($session['type']); ?></p>
                            <a href="/commission/sessions/details/<?= e($session['id']); ?>" class="link-secondary mt-md">Voir les détails de la session</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune session historique à afficher.</p>
        <?php endif; ?>
    </section>

    <section class="section-rapports-traites admin-card mt-xl">
        <h2 class="section-title">Rapports Évalués et Validés</h2>
        <?php if (!empty($rapports_traites_historique)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Numéro Rapport</th>
                    <th>Titre du Rapport</th>
                    <th>Étudiant</th>
                    <th>Date Validation</th>
                    <th>Décision Finale</th>
                    <th>Note Finale</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rapports_traites_historique as $rapport): ?>
                    <tr>
                        <td><?= e($rapport['numero_rapport']); ?></td>
                        <td><?= e(mb_strimwidth($rapport['titre'], 0, 50, '...')); ?></td>
                        <td><?= e($rapport['etudiant_nom']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($rapport['date_validation']))); ?></td>
                        <td>
                                <span class="decision-status decision-status-<?= e(strtolower(str_replace(' ', '-', $rapport['decision_finale']))); ?>">
                                    <?= e($rapport['decision_finale']); ?>
                                </span>
                        </td>
                        <td>
                                <span class="note-value <?= $rapport['note_finale'] < 10 ? 'note-fail' : ($rapport['note_finale'] >= 15 ? 'note-excellent' : 'note-pass'); ?>">
                                    <?= e(number_format($rapport['note_finale'], 2, ',', '')); ?> / 20
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/commission/rapports/details/<?= e($rapport['id']); ?>" class="btn-action view-btn" title="Consulter le rapport">
                                <span class="material-icons">visibility</span>
                            </a>
                            <?php if ($rapport['pv_id']): ?>
                                <a href="/commission/pv/consulter/<?= e($rapport['pv_id']); ?>" class="btn-action view-pv-btn" title="Consulter le Procès-Verbal">
                                    <span class="material-icons">description</span>
                                </a>
                            <?php endif; ?>
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
            <p class="text-center text-muted">Aucun rapport traité à afficher dans l'historique.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const historyFilterForm = document.getElementById('historyFilterForm');
        if (historyFilterForm) {
            historyFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(historyFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/commission/historique?${queryParams.toString()}`;
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
    /* Styles spécifiques pour historique_commission.php */
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


    /* Grille des sessions historiques (réutilisée de dashboard_commission.php) */
    .sessions-list-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-md);
    }

    .session-card {
        background-color: var(--primary-white);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .session-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-sm);
        border-bottom: 1px solid var(--border-light);
        padding-bottom: var(--spacing-xs);
    }

    .session-card .card-title {
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        margin: 0;
        font-weight: var(--font-weight-semibold);
    }

    .session-card .card-body p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
    }

    .session-card .card-body strong {
        color: var(--text-primary);
    }

    .session-card .link-secondary {
        align-self: flex-end; /* Aligne le lien en bas à droite de la carte */
    }

    /* Statuts des sessions (réutilisés) */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-clôturée {
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }
    .status-active { /* Au cas où une session active apparaîtrait aussi ici */
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }


    /* Tableaux de rapports traités - réutilisés */
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

    .btn-action.view-pv-btn { color: var(--primary-green); } /* Bouton pour voir le PV */
    .btn-action.view-pv-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    /* Statuts de décision finale (réutilisés) */
    .decision-status {
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .decision-status-approuvé-en-l-état { color: var(--primary-green-dark); }
    .decision-status-approuvé-sous-réserve { color: var(--accent-yellow-dark); }
    .decision-status-refusé { color: var(--accent-red-dark); }


    /* Style pour la note (réutilisé) */
    .note-value {
        font-weight: var(--font-weight-bold);
        padding: 0.2em 0.5em;
        border-radius: var(--border-radius-sm);
    }

    .note-fail { background-color: var(--accent-red-light); color: var(--accent-red-dark); }
    .note-pass { background-color: var(--primary-blue-light); color: var(--primary-blue-dark); }
    .note-excellent { background-color: var(--primary-green-light); color: var(--primary-green-dark); }


    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>