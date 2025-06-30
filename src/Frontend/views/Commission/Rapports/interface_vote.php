<?php
// src/Frontend/views/Commission/Rapports/interface_vote.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le rapport et les options de vote (proviennent du contrôleur ValidationRapportController)
// Ces données sont des exemples pour structurer la vue.
//
//

$rapport_details = $data['rapport_details'] ?? [
    'id' => 1,
    'numero_rapport' => 'RAP-2025-0045',
    'titre' => 'Optimisation des Processus Logistiques par l\'Intégration de l\'IA',
    'etudiant_nom_complet' => 'Dupont Jean (ETU-2025-0001)',
    'session_libelle' => 'Session de Validation Juin 2025 - Vague 1',
    'statut_validation_commission' => 'En attente d\'évaluation', // Statut actuel du rapport dans le cycle de validation
];

// Options de vote disponibles
$options_vote = $data['options_vote'] ?? [
    ['code' => 'APPROUVE_ETAT', 'libelle' => 'Approuvé en l\'état'],
    ['code' => 'APPROUVE_RESERVE', 'libelle' => 'Approuvé sous réserve de corrections mineures'],
    ['code' => 'REFUSE', 'libelle' => 'Refusé'],
    ['code' => 'NECESSITE_DISCUSSION', 'libelle' => 'Nécessite une discussion collégiale approfondie'],
];

// Vote existant de l'utilisateur courant pour ce rapport et cette session (si déjà voté)
$existing_vote = $data['existing_vote'] ?? null; // Ex: ['decision_code' => 'APPROUVE_RESERVE', 'commentaire' => 'Commentaire existant']
?>

<div class="admin-module-container">
    <h1 class="admin-title">Évaluation et Vote sur Rapport</h1>

    <section class="section-rapport-info admin-card">
        <h2 class="section-title">Rapport à Évaluer : <?= e($rapport_details['titre']); ?></h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Numéro Rapport :</strong> <span><?= e($rapport_details['numero_rapport']); ?></span>
            </div>
            <div class="info-item">
                <strong>Étudiant :</strong> <span><?= e($rapport_details['etudiant_nom_complet']); ?></span>
            </div>
            <div class="info-item">
                <strong>Session :</strong> <span><?= e($rapport_details['session_libelle']); ?></span>
            </div>
            <div class="info-item">
                <a href="/commission/rapports/details/<?= e($rapport_details['id']); ?>" class="link-secondary">
                    <span class="material-icons">visibility</span> Consulter le rapport complet
                </a>
            </div>
        </div>
    </section>

    <section class="section-vote-form admin-card mt-xl">
        <h2 class="section-title">Votre Avis et Décision</h2>
        <?php if ($existing_vote): ?>
            <div class="alert alert-info mb-lg">
                <span class="material-icons">info</span>
                Vous avez déjà voté pour ce rapport. Vous pouvez modifier votre décision.
                <br> Votre décision actuelle : <strong><?= e(array_values(array_filter($options_vote, fn($o) => $o['code'] === $existing_vote['decision_code']))[0]['libelle'] ?? $existing_vote['decision_code']); ?></strong>
            </div>
        <?php endif; ?>

        <form id="formVoteRapport" action="/commission/rapports/vote/submit/<?= e($rapport_details['id']); ?>" method="POST">
            <div class="form-group vote-options-group">
                <label>Choisissez votre décision :</label>
                <?php foreach ($options_vote as $option): ?>
                    <label class="radio-option">
                        <input type="radio" name="decision_code" value="<?= e($option['code']); ?>" required
                            <?= ($existing_vote['decision_code'] ?? '') === $option['code'] ? 'checked' : ''; ?>>
                        <span><?= e($option['libelle']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-group" id="commentaire_vote_group" style="display:none;">
                <label for="commentaire_vote">Commentaire / Justification (obligatoire pour certaines décisions) :</label>
                <textarea id="commentaire_vote" name="commentaire" rows="8" placeholder="Expliquez votre décision et proposez des recommandations..."><?= e($existing_vote['commentaire'] ?? ''); ?></textarea>
                <small class="form-help">Ce commentaire sera visible par l'étudiant si votre décision le justifie.</small>
            </div>

            <button type="submit" class="btn btn-primary-green">
                <span class="material-icons">how_to_vote</span> Enregistrer mon Vote
            </button>
            <a href="/commission/rapports/liste" class="btn btn-secondary-gray ml-md">
                <span class="material-icons">cancel</span> Annuler
            </a>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const voteOptionsGroup = document.querySelector('.vote-options-group');
        const commentaireVoteGroup = document.getElementById('commentaire_vote_group');
        const commentaireVoteTextarea = document.getElementById('commentaire_vote');
        const formVoteRapport = document.getElementById('formVoteRapport');

        const decisionsRequiringComment = ['APPROUVE_RESERVE', 'REFUSE', 'NECESSITE_DISCUSSION'];

        function toggleCommentField() {
            const selectedDecision = document.querySelector('input[name="decision_code"]:checked');
            if (selectedDecision && decisionsRequiringComment.includes(selectedDecision.value)) {
                commentaireVoteGroup.style.display = 'flex';
                commentaireVoteTextarea.setAttribute('required', 'required');
            } else {
                commentaireVoteGroup.style.display = 'none';
                commentaireVoteTextarea.removeAttribute('required');
            }
        }

        if (voteOptionsGroup) {
            voteOptionsGroup.addEventListener('change', toggleCommentField);
            // Initialiser l'état au chargement de la page
            toggleCommentField();
        }

        if (formVoteRapport) {
            formVoteRapport.addEventListener('submit', function(event) {
                // Re-valider la présence du commentaire si nécessaire
                const selectedDecision = document.querySelector('input[name="decision_code"]:checked');
                if (selectedDecision && decisionsRequiringComment.includes(selectedDecision.value)) {
                    if (commentaireVoteTextarea.value.trim() === '') {
                        alert('Veuillez saisir un commentaire pour justifier votre décision.');
                        event.preventDefault();
                        return;
                    }
                }
                console.log("Formulaire de vote soumis.");
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
    /* Styles spécifiques pour interface_vote.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px; /* Adapté à une interface de vote */
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

    /* Informations du rapport */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

    .info-item span {
        color: var(--text-secondary);
    }

    .link-secondary {
        color: var(--primary-blue);
        text-decoration: none;
        transition: color var(--transition-fast);
        font-weight: var(--font-weight-medium);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
    }

    .link-secondary:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }

    /* Formulaire de vote */
    .section-vote-form .section-title {
        text-align: center;
    }

    .vote-options-group {
        margin-bottom: var(--spacing-lg);
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
    }

    .vote-options-group label {
        display: flex;
        align-items: center;
        font-size: var(--font-size-base);
        color: var(--text-primary);
        cursor: pointer;
        margin-bottom: 0; /* Réinitialise la marge du label par défaut du form-group */
    }

    .vote-options-group input[type="radio"] {
        margin-right: var(--spacing-sm);
        transform: scale(1.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 150px;
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    .form-group textarea:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    /* Boutons de soumission */
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

    /* Alertes (réutilisées) */
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

    .alert .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    /* Utilitaires */
    .mb-lg { margin-bottom: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-md { margin-top: var(--spacing-md); }
</style>