<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/manage_penalites.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données (étudiants, types de pénalités, pénalités existantes)
// proviennent du ScolariteController.
//
//

$etudiants_disponibles = $data['etudiants_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Dupont Jean (ETU-2025-0001)'],
    ['id' => 2, 'nom_complet' => 'Curie Marie (ETU-2025-0002)'],
    ['id' => 3, 'nom_complet' => 'Voltaire François (ETU-2025-0003)'],
];

$types_penalites = $data['types_penalites'] ?? [
    ['code' => 'FINANCIERE', 'libelle' => 'Financière (Montant fixe)'],
    ['code' => 'ADMINISTRATIVE', 'libelle' => 'Administrative (Blocage soumission)'],
];

$statuts_penalite_filtre = $data['statuts_penalite_filtre'] ?? [
    'ALL' => 'Tous les statuts', 'En attente' => 'En attente', 'Régularisée' => 'Régularisée', 'Annulée' => 'Annulée'
];

$penalites_enregistrees = $data['penalites_enregistrees'] ?? [
    ['id' => 1, 'etudiant_nom' => 'Dupont Jean', 'type' => 'FINANCIERE', 'montant' => 500000, 'motif' => 'Retard soumission rapport (2 ans)', 'date_penalite' => '2025-01-01', 'statut' => 'En attente'],
    ['id' => 2, 'etudiant_nom' => 'Curie Marie', 'type' => 'ADMINISTRATIVE', 'montant' => null, 'motif' => 'Non respect procédure stage', 'date_penalite' => '2024-11-15', 'statut' => 'Régularisée'],
    ['id' => 3, 'etudiant_nom' => 'Voltaire François', 'type' => 'FINANCIERE', 'montant' => 250000, 'motif' => 'Retard paiement frais', 'date_penalite' => '2025-05-10', 'statut' => 'En attente'],
];

// ID étudiant éventuellement passé en paramètre GET pour pré-sélection
$selected_etudiant_id = $_GET['etudiant_id'] ?? null;
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Gestion des Pénalités Étudiantes</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Appliquer une Nouvelle Pénalité</h2>
        <form id="applyPenaltyForm" action="/personnel/scolarite/manage-penalites/apply" method="POST">
            <div class="form-group">
                <label for="etudiant_id">Étudiant concerné :</label>
                <select id="etudiant_id" name="etudiant_id" required>
                    <option value="">Sélectionner un étudiant</option>
                    <?php foreach ($etudiants_disponibles as $etudiant): ?>
                        <option value="<?= e($etudiant['id']); ?>"
                            <?= ($selected_etudiant_id == $etudiant['id']) ? 'selected' : ''; ?>>
                            <?= e($etudiant['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="type_penalite">Type de Pénalité :</label>
                <select id="type_penalite" name="type_penalite" required>
                    <option value="">Sélectionner un type</option>
                    <?php foreach ($types_penalites as $type): ?>
                        <option value="<?= e($type['code']); ?>"><?= e($type['libelle']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="montant_penalty_group" style="display:none;">
                <label for="montant_penalite">Montant de la pénalité (FCFA) :</label>
                <input type="number" id="montant_penalite" name="montant_penalite" min="0" step="1">
            </div>
            <div class="form-group">
                <label for="motif_penalite">Motif de la Pénalité :</label>
                <textarea id="motif_penalite" name="motif_penalite" rows="4" required placeholder="Ex: Retard de soumission du rapport de X mois, Non-respect de la procédure Y..."></textarea>
            </div>
            <div class="form-group">
                <label for="date_penalite">Date d'Application :</label>
                <input type="date" id="date_penalite" name="date_penalite" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="form-actions mt-lg">
                <button type="submit" class="btn btn-primary-blue">
                    <span class="material-icons">gpp_bad</span> Appliquer la Pénalité
                </button>
            </div>
        </form>
    </section>

    <section class="section-filters admin-card mt-xl">
        <h2 class="section-title">Filtrer les Pénalités</h2>
        <form id="penaltyFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_etudiant_id">Étudiant :</label>
                <select id="filter_etudiant_id" name="etudiant_id">
                    <option value="ALL">Tous les étudiants</option>
                    <?php foreach ($etudiants_disponibles as $etudiant): ?>
                        <option value="<?= e($etudiant['id']); ?>" <?= (($_GET['etudiant_id'] ?? 'ALL') == $etudiant['id']) ? 'selected' : ''; ?>>
                            <?= e($etudiant['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_statut_penalite">Statut :</label>
                <select id="filter_statut_penalite" name="statut_penalite">
                    <?php foreach ($statuts_penalite_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['statut_penalite'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/scolarite/manage-penalites'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Historique des Pénalités</h2>
        <?php if (!empty($penalites_enregistrees)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Motif</th>
                    <th>Date Application</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($penalites_enregistrees as $penalite): ?>
                    <tr>
                        <td><?= e($penalite['etudiant_nom']); ?></td>
                        <td><?= e($penalite['type']); ?></td>
                        <td><?= $penalite['montant'] ? e(number_format($penalite['montant'], 2, ',', ' ')) . ' FCFA' : 'N/A'; ?></td>
                        <td><?= e(mb_strimwidth($penalite['motif'], 0, 50, '...')); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($penalite['date_penalite']))); ?></td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(e(str_replace(' ', '-', $penalite['statut']))); ?>">
                                    <?= e($penalite['statut']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <?php if ($penalite['statut'] === 'En attente'): ?>
                                <form action="/personnel/scolarite/manage-penalites/regularize/<?= e($penalite['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Confirmer la régularisation de cette pénalité ?');">
                                    <button type="submit" class="btn-action regularize-btn" title="Marquer comme régularisée">
                                        <span class="material-icons">check_circle_outline</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form action="/personnel/scolarite/manage-penalites/delete/<?= e($penalite['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Supprimer cette pénalité ? Cette action est irréversible.');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer">
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
            <p class="text-center text-muted">Aucune pénalité enregistrée pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typePenaliteSelect = document.getElementById('type_penalite');
        const montantPenaltyGroup = document.getElementById('montant_penalty_group');
        const montantPenaliteInput = document.getElementById('montant_penalite');
        const applyPenaltyForm = document.getElementById('applyPenaltyForm');

        function toggleMontantField() {
            if (typePenaliteSelect.value === 'FINANCIERE') {
                montantPenaltyGroup.style.display = 'flex';
                montantPenaliteInput.setAttribute('required', 'required');
            } else {
                montantPenaltyGroup.style.display = 'none';
                montantPenaliteInput.removeAttribute('required');
                montantPenaliteInput.value = ''; // Vider le champ si non pertinent
            }
        }

        if (typePenaliteSelect) {
            typePenaliteSelect.addEventListener('change', toggleMontantField);
            toggleMontantField(); // Initialiser à l'ouverture de la page
        }

        if (applyPenaltyForm) {
            applyPenaltyForm.addEventListener('submit', function(event) {
                const etudiantId = document.getElementById('etudiant_id').value;
                const typePenalite = document.getElementById('type_penalite').value;
                const motifPenalite = document.getElementById('motif_penalite').value.trim();
                const datePenalite = document.getElementById('date_penalite').value;

                if (!etudiantId || !typePenalite || !motifPenalite || !datePenalite) {
                    alert('Veuillez remplir tous les champs obligatoires pour appliquer la pénalité.');
                    event.preventDefault();
                    return;
                }

                if (typePenalite === 'FINANCIERE') {
                    const montant = parseFloat(montantPenaliteInput.value);
                    if (isNaN(montant) || montant <= 0) {
                        alert('Le montant de la pénalité financière doit être un nombre positif.');
                        event.preventDefault();
                        return;
                    }
                }
                console.log("Formulaire d'application de pénalité soumis.");
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
    /* Styles spécifiques pour manage_penalites.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1000px;
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

    /* Formulaires - réutilisation */
    .form-group {
        margin-bottom: var(--spacing-md);
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
    .form-group input[type="number"],
    .form-group input[type="date"],
    .form-group select,
    .form-group textarea {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* Boutons d'action */
    .form-actions {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin-top: var(--spacing-xl);
    }

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

    .btn-action.regularize-btn { color: var(--primary-green); } /* Bouton Régulariser */
    .btn-action.regularize-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }


    /* Statuts de pénalité */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }

    .status-en-attente {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-régularisée {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-annulée {
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }


    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>