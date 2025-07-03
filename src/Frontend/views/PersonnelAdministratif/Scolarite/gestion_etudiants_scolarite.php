<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/gestion_etudiants_scolarite.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les étudiants (proviennent du ScolariteController)
//
//

$etudiants_scolarite = $data['etudiants_scolarite'] ?? [
    ['id' => 1, 'matricule' => 'ETU-2025-0001', 'nom_complet' => 'Dupont Jean', 'niveau_etude' => 'Master 2', 'specialite' => 'MIAGE', 'eligible_soumission' => true, 'status_compte' => 'Actif', 'penalites_reglees' => true],
    ['id' => 2, 'matricule' => 'ETU-2025-0002', 'nom_complet' => 'Curie Marie', 'niveau_etude' => 'Master 2', 'specialite' => 'Informatique Scientifique', 'eligible_soumission' => false, 'status_compte' => 'Inactif', 'penalites_reglees' => false],
    ['id' => 3, 'matricule' => 'ETU-2025-0003', 'nom_complet' => 'Voltaire François', 'niveau_etude' => 'Licence 3', 'specialite' => 'Cybersécurité', 'eligible_soumission' => true, 'status_compte' => 'Actif', 'penalites_reglees' => true],
    ['id' => 4, 'matricule' => 'ETU-2025-0004', 'nom_complet' => 'Rousseau Sophie', 'niveau_etude' => 'Master 1', 'specialite' => 'MIAGE', 'eligible_soumission' => true, 'status_compte' => 'Actif', 'penalites_reglees' => true],
];

// Options de filtrage
$niveaux_etude_filtre = $data['niveaux_etude_filtre'] ?? [
    'ALL' => 'Tous les niveaux', 'Licence 1' => 'Licence 1', 'Licence 2' => 'Licence 2', 'Licence 3' => 'Licence 3', 'Master 1' => 'Master 1', 'Master 2' => 'Master 2'
];
$specialites_filtre = $data['specialites_filtre'] ?? [
    'ALL' => 'Toutes les spécialités', 'MIAGE' => 'MIAGE', 'Informatique Scientifique' => 'Informatique Scientifique', 'Cybersécurité' => 'Cybersécurité'
];
$eligibilite_filtre = $data['eligibilite_filtre'] ?? [
    'ALL' => 'Tous les statuts', 'Eligible' => 'Éligible Soumission', 'NonEligible' => 'Non Éligible Soumission', 'AvecPenalites' => 'Avec Pénalités'
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Gestion des Étudiants (Scolarité)</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Étudiants</h2>
        <form id="etudiantScolariteFilterForm" class="filter-form">
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
                <label for="filter_specialite">Spécialité :</label>
                <select id="filter_specialite" name="specialite">
                    <?php foreach ($specialites_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['specialite'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_eligibilite">Éligibilité Soumission :</label>
                <select id="filter_eligibilite" name="eligibilite_soumission">
                    <?php foreach ($eligibilite_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['eligibilite_soumission'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_keyword">Recherche (Nom, Matricule) :</label>
                <input type="text" id="filter_keyword" name="keyword" value="<?= e($_GET['keyword'] ?? ''); ?>" placeholder="Rechercher...">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/personnel/scolarite/gestion-etudiants'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Liste des Étudiants</h2>
        <?php if (!empty($etudiants_scolarite)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom Complet</th>
                    <th>Niveau d'Étude</th>
                    <th>Spécialité</th>
                    <th>Éligibilité Soumission</th>
                    <th>Statut Compte</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($etudiants_scolarite as $etudiant): ?>
                    <tr>
                        <td><?= e($etudiant['matricule']); ?></td>
                        <td><?= e($etudiant['nom_complet']); ?></td>
                        <td><?= e($etudiant['niveau_etude']); ?></td>
                        <td><?= e($etudiant['specialite']); ?></td>
                        <td>
                                <span class="status-indicator status-<?= $etudiant['eligible_soumission'] ? 'eligible' : ($etudiant['penalites_reglees'] ? 'non-eligible' : 'penalite'); ?>">
                                    <?= $etudiant['eligible_soumission'] ? 'Éligible' : ($etudiant['penalites_reglees'] ? 'Non Éligible' : 'Pénalités'); ?>
                                </span>
                        </td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(e($etudiant['status_compte'])); ?>">
                                    <?= e($etudiant['status_compte']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <a href="/admin/utilisateurs/etudiant/edit/<?= e($etudiant['id']); ?>" class="btn-action edit-btn" title="Modifier profil étudiant">
                                <span class="material-icons">edit</span>
                            </a>
                            <?php if (!$etudiant['eligible_soumission'] && !$etudiant['penalites_reglees']): ?>
                                <a href="/personnel/scolarite/manage-penalites?etudiant_id=<?= e($etudiant['id']); ?>" class="btn-action penalties-btn" title="Gérer les pénalités">
                                    <span class="material-icons">gpp_bad</span>
                                </a>
                            <?php endif; ?>
                            <?php if ($etudiant['status_compte'] === 'Inactif' && $etudiant['eligible_soumission']): ?>
                                <form action="/personnel/scolarite/activate-account/<?= e($etudiant['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Activer le compte de cet étudiant ?');">
                                    <button type="submit" class="btn-action activate-btn" title="Activer le compte">
                                        <span class="material-icons">person_add_alt_1</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="/personnel/scolarite/gestion-inscriptions?etudiant_id=<?= e($etudiant['id']); ?>" class="btn-action manage-inscriptions-btn" title="Gérer les inscriptions">
                                <span class="material-icons">how_to_reg</span>
                            </a>
                            <a href="/personnel/scolarite/liste-notes?etudiant_id=<?= e($etudiant['id']); ?>" class="btn-action manage-notes-btn" title="Gérer les notes">
                                <span class="material-icons">grade</span>
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
            <p class="text-center text-muted">Aucun étudiant enregistré pour la gestion de scolarité.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des filtres
        const etudiantScolariteFilterForm = document.getElementById('etudiantScolariteFilterForm');
        if (etudiantScolariteFilterForm) {
            etudiantScolariteFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(etudiantScolariteFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/personnel/scolarite/gestion-etudiants?${queryParams.toString()}`;
            });
        }

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }

        // Fonction générique pour confirmer les actions (déjà dans main.js, mais rappelé pour ce contexte)
        // window.confirmAction = function(title, message, actionUrl) { ... };
    });
</script>

<style>
    /* Styles spécifiques pour gestion_etudiants_scolarite.php */
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

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.penalties-btn { color: var(--accent-red); } /* Gérer pénalités */
    .btn-action.penalties-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .btn-action.activate-btn { color: var(--primary-green); } /* Activer compte */
    .btn-action.activate-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    .btn-action.manage-inscriptions-btn, .btn-action.manage-notes-btn { color: var(--accent-violet); } /* Gérer inscriptions/notes */
    .btn-action.manage-inscriptions-btn:hover, .btn-action.manage-notes-btn:hover { background-color: rgba(139, 92, 246, 0.1); }


    /* Statuts spécifiques (éligibilité, compte) */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-eligible { /* Éligible Soumission */
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-non-eligible { /* Non Éligible Soumission */
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-penalite { /* Pénalités en cours */
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .status-actif { /* Statut Compte Actif */
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-inactif { /* Statut Compte Inactif */
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }

    .status-bloqué { /* Statut Compte Bloqué */
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>