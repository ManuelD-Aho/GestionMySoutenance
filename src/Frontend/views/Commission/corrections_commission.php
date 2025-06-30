<?php
// src/Frontend/views/Commission/corrections_commission.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les rapports nécessitant des corrections (proviennent du contrôleur CorrectionCommissionController)
// Ces données sont des exemples pour structurer la vue.
//

$rapports_avec_corrections = $data['rapports_avec_corrections'] ?? [
    ['id' => 1, 'numero_rapport' => 'RAP-2025-0045', 'titre' => 'Optimisation Logistique par IA', 'etudiant_nom_complet' => 'Dupont Jean', 'date_soumission_correction' => '2025-07-05', 'decision_initiale' => 'Approuvé sous réserve', 'statut_corrections' => 'Soumises, à vérifier'],
    ['id' => 2, 'numero_rapport' => 'RAP-2025-0046', 'titre' => 'Analyse de Données Financières', 'etudiant_nom_complet' => 'Curie Marie', 'date_soumission_correction' => null, 'decision_initiale' => 'Refusé', 'statut_corrections' => 'Corrections en cours'],
    ['id' => 3, 'numero_rapport' => 'RAP-2025-0047', 'titre' => 'Sécurité des Applications Web', 'etudiant_nom_complet' => 'Voltaire François', 'date_soumission_correction' => '2025-07-01', 'decision_initiale' => 'Nécessite discussion', 'statut_corrections' => 'Validées (attente PV)'],
];

// Options de filtrage pour les statuts des corrections
$statuts_corrections_filtre = $data['statuts_corrections_filtre'] ?? [
    'ALL' => 'Tous les statuts',
    'Soumises, à vérifier' => 'Soumises, à vérifier',
    'Corrections en cours' => 'Corrections en cours',
    'Validées (attente PV)' => 'Validées (attente PV)',
    'Non Validées' => 'Non Validées',
];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Corrections de Rapports</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Rapports avec Corrections</h2>
        <form id="correctionsFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_status">Statut des Corrections :</label>
                <select id="filter_status" name="statut">
                    <?php foreach ($statuts_corrections_filtre as $code => $libelle): ?>
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
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/commission/corrections'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Rapports avec Corrections à Vérifier</h2>
        <?php if (!empty($rapports_avec_corrections)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Numéro Rapport</th>
                    <th>Titre du Rapport</th>
                    <th>Étudiant</th>
                    <th>Décision Initiale</th>
                    <th>Date Soumission Correction</th>
                    <th>Statut Corrections</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rapports_avec_corrections as $rapport): ?>
                    <tr>
                        <td><?= e($rapport['numero_rapport']); ?></td>
                        <td><?= e(mb_strimwidth($rapport['titre'], 0, 50, '...')); ?></td>
                        <td><?= e($rapport['etudiant_nom_complet']); ?></td>
                        <td><span class="decision-status decision-status-<?= e(strtolower(str_replace(' ', '-', $rapport['decision_initiale']))); ?>"><?= e($rapport['decision_initiale']); ?></span></td>
                        <td><?= $rapport['date_soumission_correction'] ? e(date('d/m/Y', strtotime($rapport['date_soumission_correction']))) : 'N/A'; ?></td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(str_replace(' ', '-', e($rapport['statut_corrections']))); ?>">
                                    <?= e($rapport['statut_corrections']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/commission/rapports/details/<?= e($rapport['id']); ?>" class="btn-action view-btn" title="Consulter le rapport (original et corrigé)">
                                <span class="material-icons">visibility</span>
                            </a>
                            <?php if ($rapport['statut_corrections'] === 'Soumises, à vérifier'): ?>
                                <button type="button" class="btn-action check-corrections-btn" title="Vérifier et valider les corrections"
                                        onclick="confirmAction('Valider Corrections', 'Confirmez-vous que les corrections du rapport <?= e($rapport['numero_rapport']); ?> sont satisfaisantes ?', '/commission/corrections/validate/<?= e($rapport['id']); ?>');">
                                    <span class="material-icons">assignment_turned_in</span>
                                </button>
                                <button type="button" class="btn-action reject-corrections-btn" title="Rejeter les corrections"
                                        onclick="confirmAction('Rejeter Corrections', 'Rejeter les corrections du rapport <?= e($rapport['numero_rapport']); ?> ? Cela demandera plus d\'ajustements à l\'étudiant.', '/commission/corrections/reject/<?= e($rapport['id']); ?>');">
                                    <span class="material-icons">assignment_return</span>
                                </button>
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
            <p class="text-center text-muted">Aucun rapport avec corrections à vérifier pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction générique pour confirmer les actions critiques (réutilisée)
        window.confirmAction = function(title, message, actionUrl) {
            if (confirm(message)) {
                console.log(`Action "${title}" déclenchée vers: ${actionUrl}`);
                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`${title} : Succès !`);
                            window.location.reload();
                        } else {
                            alert(`Erreur lors de ${title} : ` + (data.message || 'Erreur inconnue.'));
                        }
                    })
                    .catch(error => {
                        console.error(`Erreur AJAX lors de ${title}:`, error);
                        alert(`Une erreur de communication est survenue lors de ${title}.`);
                    });
            }
            return false;
        };

        // Logique pour la gestion des filtres
        const correctionsFilterForm = document.getElementById('correctionsFilterForm');
        if (correctionsFilterForm) {
            correctionsFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(correctionsFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/commission/corrections?${queryParams.toString()}`;
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
    /* Styles spécifiques pour corrections_commission.php */
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

    /* Filtres - réutilisés et adaptés */
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

    .btn-action.check-corrections-btn { color: var(--primary-green); } /* Pour valider les corrections */
    .btn-action.check-corrections-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    .btn-action.reject-corrections-btn { color: var(--accent-red); } /* Pour rejeter les corrections */
    .btn-action.reject-corrections-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    /* Statuts spécifiques des corrections */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 120px;
        text-align: center;
    }

    .status-soumises-a-vérifier {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-corrections-en-cours {
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }

    .status-validées-attente-pv {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-non-validées {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    /* Décision initiale du rapport (réutilisée de consulter_pv.php) */
    .decision-status {
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .decision-status-approuvé-sous-réserve {
        color: var(--accent-yellow-dark);
    }
    .decision-status-refusé {
        color: var(--accent-red-dark);
    }
    .decision-status-nécessite-discussion {
        color: var(--primary-blue-dark);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>