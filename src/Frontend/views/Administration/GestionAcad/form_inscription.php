<?php
// src/Frontend/views/Administration/GestionAcad/form_inscription.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour l'inscription, les étudiants, années académiques, niveaux d'étude et statuts de paiement
// (proviennent du contrôleur GestionAcadController).
// Ces données sont des exemples pour structurer la vue.
//

$inscription_a_modifier = $data['inscription_a_modifier'] ?? null;

$etudiants_disponibles = $data['etudiants_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Dupont Jean (ETU-2024-001)'],
    ['id' => 2, 'nom_complet' => 'Curie Marie (ETU-2024-002)'],
    ['id' => 3, 'nom_complet' => 'Voltaire François (ETU-2024-003)'],
];

$annees_academiques_disponibles = $data['annees_academiques_disponibles'] ?? [
    ['id' => 1, 'libelle' => '2023-2024'],
    ['id' => 2, 'libelle' => '2024-2025'],
];

$niveaux_etude_disponibles = $data['niveaux_etude_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'Licence 1'],
    ['id' => 2, 'libelle' => 'Licence 2'],
    ['id' => 3, 'libelle' => 'Licence 3'],
    ['id' => 4, 'libelle' => 'Master 1'],
    ['id' => 5, 'libelle' => 'Master 2'],
];

$statuts_paiement_disponibles = $data['statuts_paiement_disponibles'] ?? [
    ['code' => 'PAIE', 'libelle' => 'Payé'],
    ['code' => 'ATTENTE', 'libelle' => 'En attente'],
    ['code' => 'PARTIEL', 'libelle' => 'Partiel'],
    ['code' => 'RETARD', 'libelle' => 'En retard'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Inscriptions</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $inscription_a_modifier ? 'Modifier' : 'Enregistrer'; ?> une Inscription</h2>
        <form id="formInscription" action="/admin/gestion-acad/inscriptions/<?= $inscription_a_modifier ? 'update/' . e($inscription_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="etudiant_id">Étudiant :</label>
                <select id="etudiant_id" name="etudiant_id" required <?= $inscription_a_modifier ? 'disabled' : ''; ?>>
                    <option value="">Sélectionner un étudiant</option>
                    <?php foreach ($etudiants_disponibles as $etudiant): ?>
                        <option value="<?= e($etudiant['id']); ?>"
                            <?= ($inscription_a_modifier['etudiant_id'] ?? '') == $etudiant['id'] ? 'selected' : ''; ?>>
                            <?= e($etudiant['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($inscription_a_modifier): ?>
                    <input type="hidden" name="etudiant_id" value="<?= e($inscription_a_modifier['etudiant_id']); ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="annee_academique_id">Année Académique :</label>
                <select id="annee_academique_id" name="annee_academique_id" required <?= $inscription_a_modifier ? 'disabled' : ''; ?>>
                    <option value="">Sélectionner une année</option>
                    <?php foreach ($annees_academiques_disponibles as $annee): ?>
                        <option value="<?= e($annee['id']); ?>"
                            <?= ($inscription_a_modifier['annee_academique_id'] ?? '') == $annee['id'] ? 'selected' : ''; ?>>
                            <?= e($annee['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($inscription_a_modifier): ?>
                    <input type="hidden" name="annee_academique_id" value="<?= e($inscription_a_modifier['annee_academique_id']); ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="niveau_etude_id">Niveau d'Étude :</label>
                <select id="niveau_etude_id" name="niveau_etude_id" required>
                    <option value="">Sélectionner un niveau</option>
                    <?php foreach ($niveaux_etude_disponibles as $niveau): ?>
                        <option value="<?= e($niveau['id']); ?>"
                            <?= ($inscription_a_modifier['niveau_etude_id'] ?? '') == $niveau['id'] ? 'selected' : ''; ?>>
                            <?= e($niveau['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="frais_inscription">Montant des frais d'inscription :</label>
                <input type="number" id="frais_inscription" name="frais_inscription" min="0" step="0.01"
                       value="<?= e($inscription_a_modifier['frais_inscription'] ?? ''); ?>" required placeholder="Ex: 500000.00">
            </div>

            <div class="form-group">
                <label for="statut_paiement_code">Statut de Paiement :</label>
                <select id="statut_paiement_code" name="statut_paiement_code" required>
                    <option value="">Sélectionner un statut</option>
                    <?php foreach ($statuts_paiement_disponibles as $statut): ?>
                        <option value="<?= e($statut['code']); ?>"
                            <?= ($inscription_a_modifier['statut_paiement_code'] ?? '') == $statut['code'] ? 'selected' : ''; ?>>
                            <?= e($statut['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $inscription_a_modifier ? 'save' : 'add'; ?></span>
                <?= $inscription_a_modifier ? 'Enregistrer les modifications' : 'Enregistrer l\'Inscription'; ?>
            </button>
            <?php if ($inscription_a_modifier): ?>
                <a href="/admin/gestion-acad/inscriptions" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formInscription');
        if (form) {
            form.addEventListener('submit', function(event) {
                const frais = parseFloat(document.getElementById('frais_inscription').value);

                if (isNaN(frais) || frais < 0) {
                    alert('Le montant des frais d\'inscription doit être un nombre positif.');
                    event.preventDefault();
                    return;
                }

                console.log("Formulaire d'inscription soumis.");
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
    /* Styles spécifiques pour form_inscription.php */
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
    .form-group input[type="number"],
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

    .form-group input:focus,
    .form-group select:focus {
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