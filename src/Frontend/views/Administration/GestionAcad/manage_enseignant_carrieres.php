<?php
// src/Frontend/views/Administration/GestionAcad/manage_enseignant_carrieres.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données (enseignants, grades, fonctions) proviendraient du contrôleur GestionAcadController.
// Ces données sont des exemples pour structurer la vue.
//

$enseignants_disponibles = $data['enseignants_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Dr. Dubois Antoine (ENS-2024-001)'],
    ['id' => 2, 'nom_complet' => 'Pr. Leclerc Sophie (ENS-2024-002)'],
    ['id' => 3, 'nom_complet' => 'Mme. Martin Isabelle (ENS-2024-003)'],
];

$grades_disponibles = $data['grades_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'Maître de Conférences'],
    ['id' => 2, 'libelle' => 'Professeur'],
    ['id' => 3, 'libelle' => 'Assistant'],
];

$fonctions_disponibles = $data['fonctions_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'Responsable de Spécialité MIAGE'],
    ['id' => 2, 'libelle' => 'Chef de Département Informatique'],
    ['id' => 3, 'libelle' => 'Directeur de Laboratoire'],
];

// Données fictives pour l'historique de carrière d'un enseignant sélectionné
$enseignant_selectionne_id = $data['enseignant_selectionne_id'] ?? null;
$historique_carrieres = $data['historique_carrieres'] ?? [
    'grades' => [
        ['id' => 101, 'grade_libelle' => 'Maître de Conférences', 'date_acquisition' => '2018-09-01'],
        ['id' => 102, 'grade_libelle' => 'Professeur', 'date_acquisition' => '2023-01-01'],
    ],
    'fonctions' => [
        ['id' => 201, 'fonction_libelle' => 'Responsable de Spécialité MIAGE', 'date_debut' => '2020-09-01', 'date_fin' => '2023-08-31'],
        ['id' => 202, 'fonction_libelle' => 'Chef de Département Informatique', 'date_debut' => '2023-09-01', 'date_fin' => null], // null = fonction actuelle
    ],
];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Carrières Enseignants</h1>

    <section class="section-selection-enseignant admin-card">
        <h2 class="section-title">Sélectionner un Enseignant</h2>
        <div class="form-group">
            <label for="enseignant_id_select">Enseignant :</label>
            <select id="enseignant_id_select" name="enseignant_id_select" required>
                <option value="">Sélectionner un enseignant</option>
                <?php foreach ($enseignants_disponibles as $enseignant): ?>
                    <option value="<?= e($enseignant['id']); ?>"
                        <?= ($enseignant_selectionne_id == $enseignant['id']) ? 'selected' : ''; ?>>
                        <?= e($enseignant['nom_complet']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="loadEnseignantCareer" class="btn btn-primary-blue mt-md">
                <span class="material-icons">search</span> Charger la Carrière
            </button>
        </div>
    </section>

    <?php if ($enseignant_selectionne_id): ?>
        <div id="enseignantCareerDetails">
            <section class="section-grades admin-card mt-xl">
                <h2 class="section-title">Historique des Grades</h2>
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Date d'Acquisition</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($historique_carrieres['grades'])): ?>
                        <?php foreach ($historique_carrieres['grades'] as $grade): ?>
                            <tr>
                                <td><?= e($grade['grade_libelle']); ?></td>
                                <td><?= e(date('d/m/Y', strtotime($grade['date_acquisition']))); ?></td>
                                <td class="actions">
                                    <form action="/admin/gestion-acad/carrieres/grades/delete/<?= e($enseignant_selectionne_id); ?>/<?= e($grade['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Supprimer ce grade de l\'historique ?');">
                                        <button type="submit" class="btn-action delete-btn" title="Supprimer ce grade">
                                            <span class="material-icons">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center">Aucun grade enregistré pour cet enseignant.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <form id="formAddGrade" action="/admin/gestion-acad/carrieres/grades/add/<?= e($enseignant_selectionne_id); ?>" method="POST" class="mt-lg">
                    <h3>Ajouter un Nouveau Grade</h3>
                    <div class="form-group">
                        <label for="new_grade_id">Grade :</label>
                        <select id="new_grade_id" name="grade_id" required>
                            <option value="">Sélectionner un grade</option>
                            <?php foreach ($grades_disponibles as $grade_opt): ?>
                                <option value="<?= e($grade_opt['id']); ?>"><?= e($grade_opt['libelle']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_acquisition_grade">Date d'Acquisition :</label>
                        <input type="date" id="date_acquisition_grade" name="date_acquisition" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary-blue">
                        <span class="material-icons">add</span> Ajouter Grade
                    </button>
                </form>
            </section>

            <section class="section-functions admin-card mt-xl">
                <h2 class="section-title">Historique des Fonctions</h2>
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Fonction</th>
                        <th>Date de Début</th>
                        <th>Date de Fin</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($historique_carrieres['fonctions'])): ?>
                        <?php foreach ($historique_carrieres['fonctions'] as $fonction): ?>
                            <tr>
                                <td><?= e($fonction['fonction_libelle']); ?></td>
                                <td><?= e(date('d/m/Y', strtotime($fonction['date_debut']))); ?></td>
                                <td><?= $fonction['date_fin'] ? e(date('d/m/Y', strtotime($fonction['date_fin']))) : '<span class="status-indicator status-healthy">Actuelle</span>'; ?></td>
                                <td class="actions">
                                    <?php if (!$fonction['date_fin']): // Si la fonction est actuelle, on peut la terminer ?>
                                        <form action="/admin/gestion-acad/carrieres/fonctions/end/<?= e($enseignant_selectionne_id); ?>/<?= e($fonction['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Terminer cette fonction (définir la date de fin à aujourd\'hui) ?');">
                                            <button type="submit" class="btn-action terminate-btn" title="Terminer la fonction">
                                                <span class="material-icons">event_busy</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="/admin/gestion-acad/carrieres/fonctions/delete/<?= e($enseignant_selectionne_id); ?>/<?= e($fonction['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Supprimer cette fonction de l\'historique ?');">
                                        <button type="submit" class="btn-action delete-btn" title="Supprimer cette fonction">
                                            <span class="material-icons">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Aucune fonction enregistrée pour cet enseignant.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <form id="formAddFunction" action="/admin/gestion-acad/carrieres/fonctions/add/<?= e($enseignant_selectionne_id); ?>" method="POST" class="mt-lg">
                    <h3>Ajouter une Nouvelle Fonction</h3>
                    <div class="form-group">
                        <label for="new_function_id">Fonction :</label>
                        <select id="new_function_id" name="fonction_id" required>
                            <option value="">Sélectionner une fonction</option>
                            <?php foreach ($fonctions_disponibles as $fonction_opt): ?>
                                <option value="<?= e($fonction_opt['id']); ?>"><?= e($fonction_opt['libelle']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_debut_function">Date de Début :</label>
                        <input type="date" id="date_debut_function" name="date_debut" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_fin_function">Date de Fin (laisser vide si actuelle) :</label>
                        <input type="date" id="date_fin_function" name="date_fin">
                    </div>
                    <button type="submit" class="btn btn-primary-blue">
                        <span class="material-icons">add</span> Ajouter Fonction
                    </button>
                </form>
            </section>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const enseignantSelect = document.getElementById('enseignant_id_select');
        const loadCareerButton = document.getElementById('loadEnseignantCareer');
        const enseignantCareerDetails = document.getElementById('enseignantCareerDetails');

        if (loadCareerButton) {
            loadCareerButton.addEventListener('click', function() {
                const selectedEnseignantId = enseignantSelect.value;
                if (selectedEnseignantId) {
                    // Redirige vers la même page mais avec l'ID de l'enseignant dans l'URL
                    // Le contrôleur rechargera la page avec les données de carrière de cet enseignant
                    window.location.href = `/admin/gestion-acad/manage-enseignant-carrieres?enseignant_id=${selectedEnseignantId}`;
                } else {
                    alert('Veuillez sélectionner un enseignant.');
                }
            });
        }

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }

        // Validation des dates pour le formulaire d'ajout de fonction
        const formAddFunction = document.getElementById('formAddFunction');
        if (formAddFunction) {
            formAddFunction.addEventListener('submit', function(event) {
                const dateDebut = document.getElementById('date_debut_function').value;
                const dateFin = document.getElementById('date_fin_function').value;

                if (dateDebut && dateFin && new Date(dateDebut) >= new Date(dateFin)) {
                    alert('La date de début de fonction doit être antérieure à la date de fin.');
                    event.preventDefault();
                }
            });
        }
    });
</script>

<style>
    /* Styles spécifiques pour manage_enseignant_carrieres.php */
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

    /* Formulaires et sélection d'enseignant */
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

    .form-group select,
    .form-group input[type="date"] {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
        max-width: 400px; /* Limiter la largeur des selects/inputs */
    }

    .form-group select:focus,
    .form-group input[type="date"]:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .section-selection-enseignant .btn {
        align-self: flex-start; /* Aligne le bouton sous le select */
        margin-top: var(--spacing-md);
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

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .btn-action.terminate-btn { color: var(--accent-yellow); } /* Icône pour terminer une fonction */
    .btn-action.terminate-btn:hover { background-color: rgba(245, 158, 11, 0.1); }

    /* Boutons de formulaire d'ajout */
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

    .ml-md { margin-left: var(--spacing-md); }
    .mt-md { margin-top: var(--spacing-md); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }

    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

    .status-healthy {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }
</style>