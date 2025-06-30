<?php
// src/Frontend/views/PersonnelAdministratif/Scolarite/validate_stage_form.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le stage à valider (proviennent du ScolariteController)
//
//

$stage_a_valider = $data['stage_a_valider'] ?? [
    'id' => 1,
    'etudiant_nom_complet' => 'Curie Marie (ETU-2025-0002)',
    'entreprise_nom' => 'Innovate France',
    'type_stage' => 'Pré-embauche',
    'date_debut' => '2025-03-01',
    'date_fin' => '2025-08-31',
    'description_missions' => 'Développement d\'un module de traitement de données pour leur ERP interne.',
    'valide' => false, // false = en attente ou non validé, true = validé
    'commentaires_rs' => null, // Commentaires précédents du RS
];

$is_validated = $stage_a_valider['valide'];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Validation de Stage</h1>

    <section class="section-stage-details admin-card">
        <h2 class="section-title">Détails du Stage à Valider</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Étudiant :</strong> <span><?= e($stage_a_valider['etudiant_nom_complet']); ?></span>
            </div>
            <div class="info-item">
                <strong>Entreprise :</strong> <span><?= e($stage_a_valider['entreprise_nom']); ?></span>
            </div>
            <div class="info-item">
                <strong>Type de Stage :</strong> <span><?= e($stage_a_valider['type_stage']); ?></span>
            </div>
            <div class="info-item">
                <strong>Période :</strong> <span><?= e(date('d/m/Y', strtotime($stage_a_valider['date_debut']))) . ' au ' . e(date('d/m/Y', strtotime($stage_a_valider['date_fin']))); ?></span>
            </div>
            <div class="info-item full-width-item">
                <strong>Description des Missions :</strong>
                <p><?= e($stage_a_valider['description_missions']); ?></p>
            </div>
            <div class="info-item">
                <strong>Statut actuel :</strong>
                <span class="status-indicator <?= $is_validated ? 'status-valide' : 'status-en-attente'; ?>">
                    <?= $is_validated ? 'Validé' : 'En attente de validation'; ?>
                </span>
            </div>
        </div>
    </section>

    <section class="section-validation-form admin-card mt-xl">
        <h2 class="section-title">Décision de Validation</h2>
        <?php if ($is_validated): ?>
            <div class="alert alert-success">
                <span class="material-icons">check_circle</span> Ce stage est déjà validé.
                <?php if ($stage_a_valider['commentaires_rs']): ?>
                    <p>Commentaires de validation : "<?= e($stage_a_valider['commentaires_rs']); ?>"</p>
                <?php endif; ?>
            </div>
            <div class="text-center mt-md">
                <a href="/personnel/scolarite/gestion-stages" class="btn btn-secondary-gray">
                    <span class="material-icons">list_alt</span> Retour à la liste des stages
                </a>
            </div>
        <?php else: ?>
            <form id="validateStageForm" action="/personnel/scolarite/validate-stage/<?= e($stage_a_valider['id']); ?>" method="POST">
                <div class="form-group">
                    <label for="validation_decision">Décision :</label>
                    <select id="validation_decision" name="decision" required>
                        <option value="">Sélectionner une décision</option>
                        <option value="validate">Valider ce stage</option>
                        <option value="reject">Rejeter ce stage</option>
                    </select>
                </div>
                <div class="form-group" id="validation_comment_group" style="display:none;">
                    <label for="validation_comment">Commentaire (obligatoire si rejet, facultatif si validation) :</label>
                    <textarea id="validation_comment" name="commentaire" rows="5" placeholder="Ajouter des commentaires sur la validation ou le rejet..."><?= e($stage_a_valider['commentaires_rs'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions mt-lg">
                    <button type="submit" class="btn btn-primary-green">
                        <span class="material-icons">check</span> Soumettre la Décision
                    </button>
                    <a href="/personnel/scolarite/gestion-stages" class="btn btn-secondary-gray ml-md">
                        <span class="material-icons">cancel</span> Annuler
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const validationDecisionSelect = document.getElementById('validation_decision');
        const validationCommentGroup = document.getElementById('validation_comment_group');
        const validationCommentTextarea = document.getElementById('validation_comment');
        const validateStageForm = document.getElementById('validateStageForm');

        function toggleCommentFieldAndRequired() {
            if (validationDecisionSelect.value === 'reject') {
                validationCommentGroup.style.display = 'flex';
                validationCommentTextarea.setAttribute('required', 'required');
            } else {
                validationCommentGroup.style.display = 'none';
                validationCommentTextarea.removeAttribute('required');
            }
        }

        if (validationDecisionSelect) {
            validationDecisionSelect.addEventListener('change', toggleCommentFieldAndRequired);
            toggleCommentFieldAndRequired(); // Initialiser l'état au chargement de la page
        }

        if (validateStageForm) {
            validateStageForm.addEventListener('submit', function(event) {
                // Re-valider le champ de commentaire si nécessaire
                if (validationDecisionSelect.value === 'reject') {
                    if (validationCommentTextarea.value.trim() === '') {
                        alert('Un commentaire est obligatoire si vous rejetez le stage.');
                        event.preventDefault();
                        return;
                    }
                }

                if (!confirm('Êtes-vous sûr de vouloir soumettre cette décision de validation de stage ?')) {
                    event.preventDefault();
                    return;
                }
                console.log("Formulaire de validation de stage soumis.");
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
    /* Styles spécifiques pour validate_stage_form.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px; /* Taille adaptée */
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

    /* Détails du stage */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
        box-shadow: var(--shadow-sm);
    }

    .info-item {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        line-height: var(--line-height-normal);
    }

    .info-item strong {
        color: var(--primary-blue-dark);
        display: block;
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
    }

    .info-item span, .info-item p {
        color: var(--text-secondary);
    }

    .info-item.full-width-item {
        grid-column: 1 / -1; /* Prend toute la largeur de la grille */
    }

    /* Statut de validation du stage */
    .status-indicator { /* Réutilisé des autres vues */
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 120px;
        text-align: center;
    }

    .status-valide {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-en-attente {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    /* Formulaire de décision de validation */
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
        margin-top: var(--spacing-lg); /* Moins d'espace que mt-xl pour les formulaires de décision */
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

    .btn-primary-green {
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
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }

    /* Message d'alerte (déjà stylisé globalement dans style.css) */
    .alert { /* ... */ }
    .alert-success { /* ... */ }
    .alert .material-icons { /* ... */ }
</style>