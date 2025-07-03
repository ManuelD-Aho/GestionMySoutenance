<?php
// src/Frontend/views/Administration/TransitionRole/form_delegation.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour la délégation (si modification), les utilisateurs et les traitements (permissions)
// (proviennent du contrôleur TransitionRoleController).
//

$delegation_a_modifier = $data['delegation_a_modifier'] ?? null;
// L'ID de l'utilisateur qui initie la délégation (le délégant)
$delegant_id = $data['delegant_id'] ?? ($_SESSION['user_id'] ?? null); // Par défaut, l'utilisateur connecté

$utilisateurs_disponibles = $data['utilisateurs_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Administrateur Principal (ADM-001)'],
    ['id' => 2, 'nom_complet' => 'Responsable Scolarité (RS-001)'],
    ['id' => 3, 'nom_complet' => 'Agent Conformité (AC-001)'],
    ['id' => 4, 'nom_complet' => 'Membre Commission A (CM-001)'],
    ['id' => 5, 'nom_complet' => 'Membre Commission B (CM-002)'],
];

$traitements_disponibles = $data['traitements_disponibles'] ?? [
    ['id' => 101, 'libelle' => 'Gérer les Inscriptions', 'code' => 'TRAIT_INSCRIPTION_MANAGE'],
    ['id' => 102, 'libelle' => 'Vérifier Conformité Rapport', 'code' => 'TRAIT_RAPPORT_CONFORMITE_VERIFY'],
    ['id' => 103, 'libelle' => 'Voter sur Rapport', 'code' => 'TRAIT_COMMISSION_VOTER'],
    ['id' => 104, 'libelle' => 'Rédiger PV', 'code' => 'TRAIT_PV_REDIGER'],
    ['id' => 105, 'libelle' => 'Valider PV', 'code' => 'TRAIT_PV_VALIDER'],
    ['id' => 106, 'libelle' => 'Traiter Réclamations Étudiantes', 'code' => 'TRAIT_RECLAMATION_TRAITER'],
];

// Permissions déjà déléguées pour la délégation en cours de modification
$permissions_deleguees_ids = $delegation_a_modifier['permissions_ids'] ?? [];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Délégations de Responsabilités</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $delegation_a_modifier ? 'Modifier' : 'Créer'; ?> une Délégation</h2>
        <form id="formDelegation" action="/admin/transition-role/delegations/<?= $delegation_a_modifier ? 'update/' . e($delegation_a_modifier['id']) : 'create'; ?>" method="POST">
            <input type="hidden" name="delegant_id" value="<?= e($delegant_id); ?>">

            <div class="form-group">
                <label for="delegue_id">Délégué (Bénéficiaire de la délégation) :</label>
                <select id="delegue_id" name="delegue_id" required>
                    <option value="">Sélectionner le délégué</option>
                    <?php foreach ($utilisateurs_disponibles as $utilisateur): ?>
                        <?php if ($utilisateur['id'] != $delegant_id): // Empêcher la délégation à soi-même ?>
                            <option value="<?= e($utilisateur['id']); ?>"
                                <?= ($delegation_a_modifier['delegue_id'] ?? '') == $utilisateur['id'] ? 'selected' : ''; ?>>
                                <?= e($utilisateur['nom_complet']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">La personne qui recevra les responsabilités temporairement.</small>
            </div>

            <div class="form-group">
                <label for="permissions_a_deleguer">Tâches ou Permissions à Déléguer :</label>
                <select id="permissions_a_deleguer" name="permissions_ids[]" multiple required size="8">
                    <?php foreach ($traitements_disponibles as $traitement): ?>
                        <option value="<?= e($traitement['id']); ?>"
                            <?= in_array($traitement['id'], $permissions_deleguees_ids) ? 'selected' : ''; ?>>
                            <?= e($traitement['libelle']); ?> (<?= e($traitement['code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Sélectionnez une ou plusieurs permissions que le délégué pourra exercer.</small>
            </div>

            <div class="form-group">
                <label for="date_debut">Date de Début de la Délégation :</label>
                <input type="date" id="date_debut" name="date_debut" value="<?= e($delegation_a_modifier['date_debut'] ?? date('Y-m-d')); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_fin">Date de Fin de la Délégation :</label>
                <input type="date" id="date_fin" name="date_fin" value="<?= e($delegation_a_modifier['date_fin'] ?? ''); ?>" required>
                <small class="form-help">La délégation sera automatiquement révoquée après cette date.</small>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $delegation_a_modifier ? 'save' : 'add'; ?></span>
                <?= $delegation_a_modifier ? 'Enregistrer la Délégation' : 'Créer la Délégation'; ?>
            </button>
            <?php if ($delegation_a_modifier): ?>
                <a href="/admin/transition-role/delegations" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formDelegation');
        if (form) {
            form.addEventListener('submit', function(event) {
                const delegueId = document.getElementById('delegue_id').value;
                const permissionsSelect = document.getElementById('permissions_a_deleguer');
                const dateDebut = document.getElementById('date_debut').value;
                const dateFin = document.getElementById('date_fin').value;

                if (!delegueId) {
                    alert('Veuillez sélectionner un délégué.');
                    event.preventDefault();
                    return;
                }

                if (permissionsSelect.selectedOptions.length === 0) {
                    alert('Veuillez sélectionner au moins une permission à déléguer.');
                    event.preventDefault();
                    return;
                }

                if (new Date(dateDebut) >= new Date(dateFin)) {
                    alert('La date de début doit être antérieure à la date de fin de la délégation.');
                    event.preventDefault();
                    return;
                }

                // Optionnel: Validation que le délégué n'est pas le délégant si le délégant est sélectionnable
                // if (delegueId == <?= json_encode($delegant_id); ?>) {
                //     alert('Vous ne pouvez pas vous déléguer des responsabilités à vous-même.');
                //     event.preventDefault();
                //     return;
                // }

                console.log("Formulaire de délégation soumis.");
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
    /* Styles spécifiques pour form_delegation.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 800px; /* Largeur adaptée au formulaire */
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

    .form-group input[type="date"],
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

    /* Style spécifique pour le select multiple (permissions) */
    .form-group select[multiple] {
        height: auto; /* Permet d'ajuster la hauteur en fonction du nombre d'options */
        min-height: 150px; /* Hauteur minimale */
        padding: var(--spacing-sm);
        overflow-y: auto; /* Activer le défilement si nécessaire */
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
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