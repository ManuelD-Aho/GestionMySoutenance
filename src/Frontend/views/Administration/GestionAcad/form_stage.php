<?php
// src/Frontend/views/Administration/GestionAcad/form_stage.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le stage, les étudiants, les entreprises (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//

$stage_a_modifier = $data['stage_a_modifier'] ?? null;

$etudiants_disponibles = $data['etudiants_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Dupont Jean (ETU-2024-001)'],
    ['id' => 2, 'nom_complet' => 'Curie Marie (ETU-2024-002)'],
    ['id' => 3, 'nom_complet' => 'Voltaire François (ETU-2024-003)'],
];

$entreprises_disponibles = $data['entreprises_disponibles'] ?? [
    ['id' => 1, 'nom' => 'Tech Solutions Corp'],
    ['id' => 2, 'nom' => 'Innovate France'],
    ['id' => 3, 'nom' => 'Global IT Services'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Stages</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $stage_a_modifier ? 'Modifier' : 'Enregistrer'; ?> un Stage</h2>
        <form id="formStage" action="/admin/gestion-acad/stages/<?= $stage_a_modifier ? 'update/' . e($stage_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="etudiant_id">Étudiant :</label>
                <select id="etudiant_id" name="etudiant_id" required <?= $stage_a_modifier ? 'disabled' : ''; ?>>
                    <option value="">Sélectionner un étudiant</option>
                    <?php foreach ($etudiants_disponibles as $etudiant): ?>
                        <option value="<?= e($etudiant['id']); ?>"
                            <?= ($stage_a_modifier['etudiant_id'] ?? '') == $etudiant['id'] ? 'selected' : ''; ?>>
                            <?= e($etudiant['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($stage_a_modifier): ?>
                    <input type="hidden" name="etudiant_id" value="<?= e($stage_a_modifier['etudiant_id']); ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="entreprise_id">Entreprise d'accueil :</label>
                <select id="entreprise_id" name="entreprise_id">
                    <option value="">Sélectionner une entreprise existante</option>
                    <?php foreach ($entreprises_disponibles as $entreprise): ?>
                        <option value="<?= e($entreprise['id']); ?>"
                            <?= ($stage_a_modifier['entreprise_id'] ?? '') == $entreprise['id'] ? 'selected' : ''; ?>>
                            <?= e($entreprise['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="new_entreprise">-- Nouvelle entreprise --</option>
                </select>
            </div>

            <div id="new_entreprise_fields" style="display: <?= (isset($stage_a_modifier['entreprise_id']) && !in_array($stage_a_modifier['entreprise_id'], array_column($entreprises_disponibles, 'id'))) || (!empty($_POST['entreprise_nom']) ?? false) ? 'block' : 'none'; ?>;">
                <div class="form-group">
                    <label for="entreprise_nom">Nom de la nouvelle entreprise :</label>
                    <input type="text" id="entreprise_nom" name="entreprise_nom" value="<?= e($stage_a_modifier['entreprise_nom'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="entreprise_adresse">Adresse de la nouvelle entreprise :</label>
                    <input type="text" id="entreprise_adresse" name="entreprise_adresse" value="<?= e($stage_a_modifier['entreprise_adresse'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="entreprise_contact">Contact (email/téléphone) de la nouvelle entreprise :</label>
                    <input type="text" id="entreprise_contact" name="entreprise_contact" value="<?= e($stage_a_modifier['entreprise_contact'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="date_debut">Date de Début du Stage :</label>
                <input type="date" id="date_debut" name="date_debut" value="<?= e($stage_a_modifier['date_debut'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_fin">Date de Fin du Stage :</label>
                <input type="date" id="date_fin" name="date_fin" value="<?= e($stage_a_modifier['date_fin'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="type_stage">Type de Stage :</label>
                <input type="text" id="type_stage" name="type_stage" value="<?= e($stage_a_modifier['type_stage'] ?? ''); ?>" placeholder="Ex: Stage de fin d'études, Stage ouvrier">
            </div>
            <div class="form-group">
                <label for="description_missions">Description des missions :</label>
                <textarea id="description_missions" name="description_missions" rows="5" placeholder="Détail des missions de l'étudiant durant le stage..."><?= e($stage_a_modifier['description_missions'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $stage_a_modifier ? 'save' : 'add'; ?></span>
                <?= $stage_a_modifier ? 'Enregistrer les modifications' : 'Enregistrer le Stage'; ?>
            </button>
            <?php if ($stage_a_modifier): ?>
                <a href="/admin/gestion-acad/stages" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const entrepriseSelect = document.getElementById('entreprise_id');
        const newEntrepriseFields = document.getElementById('new_entreprise_fields');
        const entrepriseNomInput = document.getElementById('entreprise_nom');
        const entrepriseAdresseInput = document.getElementById('entreprise_adresse');
        const entrepriseContactInput = document.getElementById('entreprise_contact');

        function toggleNewEntrepriseFields() {
            if (entrepriseSelect.value === 'new_entreprise') {
                newEntrepriseFields.style.display = 'block';
                entrepriseNomInput.setAttribute('required', 'required');
                entrepriseAdresseInput.setAttribute('required', 'required');
                entrepriseContactInput.setAttribute('required', 'required');
            } else {
                newEntrepriseFields.style.display = 'none';
                entrepriseNomInput.removeAttribute('required');
                entrepriseAdresseInput.removeAttribute('required');
                entrepriseContactInput.removeAttribute('required');
                // Optionnel: vider les champs si on repasse à une entreprise existante
                entrepriseNomInput.value = '';
                entrepriseAdresseInput.value = '';
                entrepriseContactInput.value = '';
            }
        }

        if (entrepriseSelect) {
            entrepriseSelect.addEventListener('change', toggleNewEntrepriseFields);
            // Exécuter au chargement pour gérer les cas de modification où une nouvelle entreprise pourrait être pré-sélectionnée
            toggleNewEntrepriseFields();
        }

        const form = document.getElementById('formStage');
        if (form) {
            form.addEventListener('submit', function(event) {
                const dateDebut = document.getElementById('date_debut').value;
                const dateFin = document.getElementById('date_fin').value;

                if (new Date(dateDebut) >= new Date(dateFin)) {
                    alert('La date de début du stage doit être antérieure à la date de fin.');
                    event.preventDefault();
                    return;
                }

                // Validation si 'Nouvelle entreprise' est sélectionnée
                if (entrepriseSelect.value === 'new_entreprise') {
                    if (entrepriseNomInput.value.trim() === '' || entrepriseAdresseInput.value.trim() === '') {
                        alert('Le nom et l\'adresse de la nouvelle entreprise sont obligatoires.');
                        event.preventDefault();
                        return;
                    }
                }
                console.log("Formulaire de stage soumis.");
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
    /* Styles spécifiques pour form_stage.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 800px;
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

    /* Formulaires - réutilisation et adaptation */
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

    .form-group input:disabled,
    .form-group select:disabled {
        background-color: var(--primary-gray-light);
        color: var(--text-light);
        cursor: not-allowed;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* Boutons - réutilisation des styles existants */
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

    .ml-md { margin-left: var(--spacing-md); }
</style>