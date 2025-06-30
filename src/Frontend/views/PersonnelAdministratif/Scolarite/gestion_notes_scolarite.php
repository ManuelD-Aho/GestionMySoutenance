<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/gestion_notes_scolarite.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les notes (proviennent du ScolariteController)
//
//

$notes_enregistrees = $data['notes_enregistrees'] ?? [
    ['id' => 1, 'etudiant_nom' => 'Dupont Jean', 'annee_academique' => '2024-2025', 'ecue_libelle' => 'INFO101 - Prog. Avancée', 'note_valeur' => 15.50, 'date_saisie' => '2025-06-20'],
    ['id' => 2, 'etudiant_nom' => 'Curie Marie', 'annee_academique' => '2024-2025', 'ecue_libelle' => 'MATH101 - Algèbre', 'note_valeur' => 12.00, 'date_saisie' => '2025-06-21'],
    ['id' => 3, 'etudiant_nom' => 'Voltaire François', 'annee_academique' => '2024-2025', 'ecue_libelle' => 'PROJ201 - Gestion de Projet', 'note_valeur' => 8.75, 'date_saisie' => '2025-06-22'],
    ['id' => 4, 'etudiant_nom' => 'Dupont Jean', 'annee_academique' => '2023-2024', 'ecue_libelle' => 'BDD301 - Bases de Données', 'note_valeur' => 10.00, 'date_saisie' => '2024-05-10'],
];

// Options de filtrage
$annees_academiques_filtre = $data['annees_academiques_filtre'] ?? [
    'ALL' => 'Toutes les années', '2024-2025' => '2024-2025', '2023-2024' => '2023-2024'
];
$ecues_filtre = $data['ecues_filtre'] ?? [
    'ALL' => 'Tous les ECUEs', 'INFO101' => 'INFO101', 'MATH101' => 'MATH101', 'PROJ201' => 'PROJ201', 'BDD301' => 'BDD301'
];
$statuts_note_filtre = $data['statuts_note_filtre'] ?? [
    'ALL' => 'Tous les statuts', 'Validée' => 'Validée', 'Non validée' => 'Non validée', 'Rattrapage' => 'Rattrapage'
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Gestion des Notes (Scolarité)</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Notes</h2>
        <form id="noteFilterForm" class="filter-form">
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
                <label for="filter_ecue">ECUE :</label>
                <select id="filter_ecue" name="ecue_code">
                    <?php foreach ($ecues_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['ecue_code'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_status">Statut de la Note :</label>
                <select id="filter_status" name="statut_note">
                    <?php foreach ($statuts_note_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['statut_note'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
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
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/scolarite/gestion-notes'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Liste des Notes</h2>
        <div class="action-buttons">
            <a href="/admin/gestion-acad/notes/create" class="btn btn-primary-green">
                <span class="material-icons">add_box</span> Saisir une Nouvelle Note
            </a>
            <a href="/personnel/scolarite/generation-documents?type=BULLETIN_NOTES_OFFICIEL" class="btn btn-secondary-gray ml-md">
                <span class="material-icons">picture_as_pdf</span> Générer Bulletins
            </a>
        </div>

        <?php if (!empty($notes_enregistrees)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Année Académique</th>
                    <th>ECUE</th>
                    <th>Note</th>
                    <th>Date de Saisie</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($notes_enregistrees as $note): ?>
                    <tr>
                        <td><?= e($note['etudiant_nom']); ?></td>
                        <td><?= e($note['annee_academique']); ?></td>
                        <td><?= e($note['ecue_libelle']); ?></td>
                        <td>
                                <span class="note-value <?= $note['note_valeur'] < 10 ? 'note-fail' : ($note['note_valeur'] >= 15 ? 'note-excellent' : 'note-pass'); ?>">
                                    <?= e(number_format($note['note_valeur'], 2, ',', '')); ?> / 20
                                </span>
                        </td>
                        <td><?= e(date('d/m/Y', strtotime($note['date_saisie']))); ?></td>
                        <td>
                                <span class="status-indicator
                                    <?php
                                if ($note['note_valeur'] >= 10) echo 'status-validée';
                                else echo 'status-non-validée'; // Simplifié, peut être plus complexe
                                ?>
                                ">
                                    <?= ($note['note_valeur'] >= 10) ? 'Validée' : 'Non validée'; ?>
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/admin/gestion-acad/notes/edit/<?= e($note['id']); ?>" class="btn-action edit-btn" title="Modifier la note">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/gestion-acad/notes/delete/<?= e($note['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Supprimer cette note ?');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer la note">
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
            <p class="text-center text-muted">Aucune note enregistrée pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/gestion-acad/notes/create" class="btn btn-primary-blue">Saisir la première note</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const noteFilterForm = document.getElementById('noteFilterForm');
        if (noteFilterForm) {
            noteFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(noteFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/personnel/scolarite/gestion-notes?${queryParams.toString()}`;
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
    /* Styles spécifiques pour gestion_notes_scolarite.php */
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

    /* Boutons de filtre et d'action */
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

    .btn-primary-green { /* Pour "Saisir une Nouvelle Note" */
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

    .ml-md { margin-left: var(--spacing-md); }

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


    /* Style pour la note et statut */
    .note-value {
        font-weight: var(--font-weight-bold);
        padding: 0.2em 0.5em;
        border-radius: var(--border-radius-sm);
    }

    .note-fail { /* Pour les notes < 10 */
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .note-pass { /* Pour les notes >= 10 et < 15 */
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .note-excellent { /* Pour les notes >= 15 */
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-indicator { /* Pour les statuts Validée / Non validée */
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }

    .status-validée {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-non-validée {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>