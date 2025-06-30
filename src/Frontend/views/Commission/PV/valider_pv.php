<?php
// src/Frontend/views/Commission/PV/valider_pv.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le PV à valider, son statut actuel et les validations des autres membres
// (proviennent du contrôleur PvController ou CommissionController).
//
//

$pv_a_valider = $data['pv_a_valider'] ?? [
    'id' => 1,
    'numero_pv' => 'PV-2025-0015',
    'date_redaction' => '2025-06-30 09:00:00',
    'rapport_titre' => 'Innovation en Systèmes Embarqués',
    'etudiant_nom_complet' => 'Marie Curie (ETU-2025-0002)',
    'president_commission_nom' => 'Pr. Martin Sophie',
    'rapporteur_nom' => 'Dr. Bernard Paul',
    'decision_finale' => 'Approuvé sous réserve de corrections mineures',
    'note_attribuee' => 14.00,
    'contenu_textuel_pv' => '<p>Le rapport de Marie Curie a été examiné. Le travail est solide mais des clarifications sont nécessaires sur la section méthodologie.</p><p><strong>Recommandations :</strong> Ajouter un diagramme de séquence pour l\'interaction des modules.</p>',
    'statut_pv_global' => 'En attente approbation', // Brouillon, En attente approbation, Validé, Rejeté
    'lien_redaction_pv' => '/commission/pv/rediger/1', // Lien vers la page de rédaction pour les suggestions
];

$validations_membres = $data['validations_membres'] ?? [
    ['membre_nom' => 'Pr. Martin Sophie (Président)', 'statut' => 'Approuvé'],
    ['membre_nom' => 'Dr. Bernard Paul (Rapporteur)', 'statut' => 'En attente'], // Le rapporteur peut ne pas avoir encore validé formellement son propre PV
    ['membre_nom' => 'Mme. Dubois Claire', 'statut' => 'Approuvé'],
    ['membre_nom' => 'M. Leclerc Anne', 'statut' => 'En attente'],
];

$current_user_has_validated = false; // Indique si l'utilisateur courant a déjà validé ce PV
$current_user_validation_status = ''; // Statut de validation de l'utilisateur courant
// Logique pour déterminer si l'utilisateur courant a déjà validé ce PV et son statut.
// Par exemple: foreach ($validations_membres as $validation) { if ($validation['id_membre'] == $_SESSION['user_id']) { $current_user_has_validated = true; $current_user_validation_status = $validation['statut']; break; } }

?>

<div class="admin-module-container">
    <h1 class="admin-title">Validation du Procès-Verbal</h1>

    <section class="section-pv-validation admin-card">
        <h2 class="section-title">PV à Valider : <?= e($pv_a_valider['numero_pv']); ?></h2>

        <div class="pv-metadata-grid mb-lg">
            <div class="meta-item">
                <strong>Rapport :</strong> <span><?= e($pv_a_valider['rapport_titre']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Étudiant :</strong> <span><?= e($pv_a_valider['etudiant_nom_complet']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Rédigé le :</strong> <span><?= e(date('d/m/Y H:i', strtotime($pv_a_valider['date_redaction']))); ?></span>
            </div>
            <div class="meta-item">
                <strong>Statut Global du PV :</strong> <span class="status-indicator status-<?= e(strtolower(str_replace(' ', '-', $pv_a_valider['statut_pv_global']))); ?>"><?= e($pv_a_valider['statut_pv_global']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Décision Proposée :</strong> <span class="decision-status decision-status-<?= e(strtolower(str_replace(' ', '-', $pv_a_valider['decision_finale']))); ?>"><?= e($pv_a_valider['decision_finale']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Note Proposée :</strong> <span class="note-value <?= $pv_a_valider['note_attribuee'] < 10 ? 'note-fail' : ($pv_a_valider['note_attribuee'] >= 15 ? 'note-excellent' : 'note-pass'); ?>"><?= e(number_format($pv_a_valider['note_attribuee'], 2, ',', '')); ?> / 20</span>
            </div>
        </div>

        <div class="pv-content-section mt-md">
            <h3>Contenu Proposé du Procès-Verbal</h3>
            <div class="pv-content-display">
                <?= $pv_a_valider['contenu_textuel_pv']; ?>
            </div>
            <div class="text-right mt-md">
                <a href="/commission/pv/consulter/<?= e($pv_a_valider['id']); ?>" class="link-secondary">Voir le PV complet</a>
                <a href="<?= e($pv_a_valider['lien_redaction_pv']); ?>" class="link-secondary ml-md">Suggérer des amendements</a>
            </div>
        </div>

        <div class="pv-validation-status mt-xl">
            <h3>Statut des Validations des Membres</h3>
            <ul class="validation-list">
                <?php foreach ($validations_membres as $validation): ?>
                    <li class="validation-item status-<?= e(strtolower(str_replace(' ', '-', $validation['statut']))); ?>">
                        <strong><?= e($validation['membre_nom']); ?> :</strong>
                        <span class="status-indicator status-<?= e(strtolower(str_replace(' ', '-', $validation['statut']))); ?>"><?= e($validation['statut']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if (!$current_user_has_validated && $pv_a_valider['statut_pv_global'] === 'En attente approbation'): ?>
            <div class="pv-validation-form mt-xl">
                <h3>Votre Décision</h3>
                <form id="formValidationPv" action="/commission/pv/validate/<?= e($pv_a_valider['id']); ?>" method="POST">
                    <div class="form-group">
                        <label for="validation_decision">Votre Décision :</label>
                        <select id="validation_decision" name="decision" required>
                            <option value="">Sélectionner une décision</option>
                            <option value="APPROUVE">Approuver</option>
                            <option value="REJETE">Rejeter</option>
                            <option value="AMENDEMENT_NECESSAIRE">Demander des Amendements</option>
                        </select>
                    </div>
                    <div class="form-group" id="commentaire_rejet_group" style="display:none;">
                        <label for="commentaire_rejet">Commentaire / Justification (obligatoire si rejet) :</label>
                        <textarea id="commentaire_rejet" name="commentaire" rows="5"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-green">
                        <span class="material-icons">check_circle</span> Soumettre votre Décision
                    </button>
                </form>
            </div>
        <?php elseif ($current_user_has_validated): ?>
            <div class="alert alert-info mt-xl">
                <span class="material-icons">info</span>
                Vous avez déjà soumis votre décision pour ce PV. Statut : <strong><?= e($current_user_validation_status); ?></strong>.
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-xl">
                <span class="material-icons">warning_amber</span>
                Ce PV n'est pas dans un état "En attente approbation" ou vous n'êtes pas autorisé à le valider.
            </div>
        <?php endif; ?>

    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const validationDecisionSelect = document.getElementById('validation_decision');
        const commentaireRejetGroup = document.getElementById('commentaire_rejet_group');
        const commentaireRejetTextarea = document.getElementById('commentaire_rejet');
        const formValidationPv = document.getElementById('formValidationPv');

        if (validationDecisionSelect) {
            validationDecisionSelect.addEventListener('change', function() {
                if (this.value === 'REJETE' || this.value === 'AMENDEMENT_NECESSAIRE') {
                    commentaireRejetGroup.style.display = 'flex';
                    commentaireRejetTextarea.setAttribute('required', 'required');
                } else {
                    commentaireRejetGroup.style.display = 'none';
                    commentaireRejetTextarea.removeAttribute('required');
                }
            });
            // Initialiser l'état au chargement de la page
            if (validationDecisionSelect.value === 'REJETE' || validationDecisionSelect.value === 'AMENDEMENT_NECESSAIRE') {
                commentaireRejetGroup.style.display = 'flex';
                commentaireRejetTextarea.setAttribute('required', 'required');
            }
        }

        if (formValidationPv) {
            formValidationPv.addEventListener('submit', function(event) {
                if (validationDecisionSelect.value === 'REJETE' || validationDecisionSelect.value === 'AMENDEMENT_NECESSAIRE') {
                    if (commentaireRejetTextarea.value.trim() === '') {
                        alert('Veuillez saisir un commentaire ou une justification pour votre décision.');
                        event.preventDefault();
                        return;
                    }
                }
                console.log("Formulaire de validation PV soumis.");
                // Le formulaire sera soumis via la méthode POST classique
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
    /* Styles spécifiques pour valider_pv.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1000px; /* Plus large pour les PV */
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

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg); /* Augmenté pour les sections sans actions directes */
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* Métadonnées du PV - réutilisées de consulter_pv.php */
    .pv-metadata-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
    }

    .meta-item {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        line-height: var(--line-height-normal);
    }

    .meta-item strong {
        color: var(--primary-blue-dark);
        display: block;
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
    }

    .meta-item span {
        color: var(--text-secondary);
    }

    /* Statuts spécifiques (réutilisation) */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 80px;
        text-align: center;
    }

    .status-validé {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-en-attente, .status-attente-approbation {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-rejeté {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .decision-status {
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .decision-status-approuvé-en-l-état { color: var(--primary-green-dark); }
    .decision-status-refusé { color: var(--accent-red-dark); }
    .decision-status-approuvé-sous-réserve-de-corrections-mineures { color: var(--accent-yellow-dark); }
    .decision-status-nécessite-discussion-collégiale-approfondie { color: var(--primary-blue-dark); }

    /* Style pour la note */
    .note-value {
        font-weight: var(--font-weight-bold);
        padding: 0.2em 0.5em;
        border-radius: var(--border-radius-sm);
    }

    .note-fail { background-color: var(--accent-red-light); color: var(--accent-red-dark); }
    .note-pass { background-color: var(--primary-blue-light); color: var(--primary-blue-dark); }
    .note-excellent { background-color: var(--primary-green-light); color: var(--primary-green-dark); }


    /* Contenu du PV - réutilisé de consulter_pv.php */
    .pv-content-section {
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
        box-shadow: var(--shadow-sm);
    }

    .pv-content-section h3 {
        font-size: var(--font-size-lg);
        color: var(--primary-green-dark);
        margin-bottom: var(--spacing-md);
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px dashed var(--border-light);
    }

    .pv-content-display {
        font-size: var(--font-size-base);
        line-height: var(--line-height-normal);
        color: var(--text-primary);
    }

    .link-secondary {
        color: var(--primary-blue);
        text-decoration: none;
        transition: color var(--transition-fast);
        font-weight: var(--font-weight-medium);
    }
    .link-secondary:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }
    .ml-md { margin-left: var(--spacing-md); }

    /* Statut des validations des membres */
    .pv-validation-status {
        margin-top: var(--spacing-xl);
    }
    .pv-validation-status h3 {
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        margin-bottom: var(--spacing-md);
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px dashed var(--border-light);
    }

    .validation-list {
        list-style: none;
        padding: 0;
    }

    .validation-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-sm) var(--spacing-md);
        margin-bottom: var(--spacing-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow-sm);
    }

    .validation-item strong {
        color: var(--text-primary);
    }

    .validation-item .status-indicator {
        min-width: 100px;
    }
    /* Couleurs de statut pour les validations individuelles */
    .validation-item.status-approuvé .status-indicator {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }
    .validation-item.status-en-attente .status-indicator {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }
    .validation-item.status-rejeté .status-indicator {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }


    /* Formulaire de décision de validation */
    .pv-validation-form {
        background-color: var(--primary-white);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-lg);
        margin-top: var(--spacing-xl);
        box-shadow: var(--shadow-md);
    }

    .pv-validation-form h3 {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        text-align: center;
        font-weight: var(--font-weight-semibold);
    }

    .form-group { /* Réutilisé des formulaires génériques */
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

    .btn-primary-green { /* Bouton de soumission de la décision */
        color: var(--text-white);
        background-color: var(--primary-green);
        margin-top: var(--spacing-lg);
    }

    .btn-primary-green:hover {
        background-color: var(--primary-green-dark);
        box-shadow: var(--shadow-sm);
    }

    /* Alertes génériques (réutilisées de auth.css et admin-module.css) */
    .alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-md);
        text-align: left;
        border: 1px solid;
    }
    .alert-info {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
        border-color: var(--primary-blue-dark);
    }
    .alert-warning {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
        border-color: var(--accent-yellow-dark);
    }
    .alert .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
</style>