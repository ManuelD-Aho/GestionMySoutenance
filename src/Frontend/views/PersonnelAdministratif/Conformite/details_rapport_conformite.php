<?php
// src/Frontend/views/PersonnelAdministratif/Conformite/details_rapport_conformite.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le rapport et la grille de conformité (proviennent du ConformiteController)
//
//

$rapport_a_verifier = $data['rapport_a_verifier'] ?? [
    'id' => 1,
    'numero_rapport' => 'RAP-2025-0045',
    'titre' => 'Optimisation des Processus Logistiques par l\'Intégration de l\'IA',
    'etudiant_nom_complet' => 'Dupont Jean (ETU-2025-0001)',
    'date_soumission' => '2025-06-28 09:00:00',
    'current_status' => 'En attente de vérification', // Peut être "En attente de vérification", "Non Conforme (corrigé)"
    'sections' => [ // Contenu textuel intégral du rapport
        ['titre' => 'Introduction', 'contenu' => '<p>Ceci est l\'introduction du rapport...</p>'],
        ['titre' => 'Développement', 'contenu' => '<p>Le développement détaille la méthodologie...</p>'],
        ['titre' => 'Conclusion', 'contenu' => '<p>En conclusion, les objectifs ont été atteints...</p>'],
        ['titre' => 'Bibliographie', 'contenu' => '<p>Références bibliographiques [1], [2]...</p>'],
    ],
    'precedente_decision_conformite' => null, // Ex: ['status' => 'Non Conforme', 'commentaire' => 'Biblio non conforme']
    'decision_agent_actuel' => null, // Si l'agent a déjà un brouillon de décision
];

$criteres_conformite = $data['criteres_conformite'] ?? [
    ['id' => 1, 'libelle' => 'Respect de la page de garde', 'code' => 'PAGE_GARDE_OK'],
    ['id' => 2, 'libelle' => 'Présence du résumé', 'code' => 'RESUME_PRESENT'],
    ['id' => 3, 'libelle' => 'Bibliographie formatée', 'code' => 'BIBLIO_FORMAT_OK'],
    ['id' => 4, 'libelle' => 'Validité du stage associée', 'code' => 'STAGE_VALIDE'],
    ['id' => 5, 'libelle' => 'Nombre de pages estimé respecté (±10%)', 'code' => 'NB_PAGES_OK'],
];

// Décisions de conformité pour les critères (peut être pré-rempli depuis $rapport_a_verifier['decision_agent_actuel'])
$criteres_evalues = [];
foreach ($criteres_conformite as $critere) {
    $criteres_evalues[$critere['id']] = $rapport_a_verifier['decision_agent_actuel']['criteres'][$critere['id']] ?? 'conforme'; // 'conforme', 'non_conforme'
}
$decision_globale_initiale = $rapport_a_verifier['decision_agent_actuel']['decision_globale'] ?? '';
$commentaire_global_initial = $rapport_a_verifier['decision_agent_actuel']['commentaire'] ?? '';

?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Vérification de Conformité du Rapport</h1>

    <section class="section-rapport-info admin-card">
        <h2 class="section-title">Rapport : <?= e($rapport_a_verifier['titre']); ?> (<?= e($rapport_a_verifier['numero_rapport']); ?>)</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Étudiant :</strong> <span><?= e($rapport_a_verifier['etudiant_nom_complet']); ?></span>
            </div>
            <div class="info-item">
                <strong>Date Soumission :</strong> <span><?= e(date('d/m/Y H:i', strtotime($rapport_a_verifier['date_soumission']))); ?></span>
            </div>
            <div class="info-item">
                <strong>Statut Actuel :</strong>
                <span class="status-indicator status-<?= strtolower(str_replace(' ', '-', e($rapport_a_verifier['current_status']))); ?>">
                    <?= e($rapport_a_verifier['current_status']); ?>
                </span>
            </div>
        </div>

        <?php if ($rapport_a_verifier['precedente_decision_conformite']): ?>
            <div class="alert alert-warning mt-lg">
                <span class="material-icons">info</span>
                Ce rapport a déjà été vérifié et jugé <strong><?= e($rapport_a_verifier['precedente_decision_conformite']['status']); ?></strong>.
                <p class="ml-md">Commentaire précédent : "<?= e($rapport_a_verifier['precedente_decision_conformite']['commentaire']); ?>"</p>
                <a href="/etudiant/rapport/suivi/<?= e($rapport_a_verifier['id']); ?>" class="btn-link ml-md">Voir le suivi de l'étudiant</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-rapport-content admin-card mt-xl">
        <h2 class="section-title">Contenu du Rapport</h2>
        <div class="rapport-sections">
            <?php foreach ($rapport_a_verifier['sections'] as $section): ?>
                <div class="rapport-section-block">
                    <h3><?= e($section['titre']); ?></h3>
                    <div class="section-content-display">
                        <?= $section['contenu']; // Contenu HTML, déjà échappé ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-lg">
            <a href="/admin/documents/view-raw-report/<?= e($rapport_a_verifier['id']); ?>" target="_blank" class="btn btn-secondary-gray">
                <span class="material-icons">visibility</span> Voir le rapport brut dans un nouvel onglet
            </a>
        </div>
    </section>

    <section class="section-conformity-check admin-card mt-xl">
        <h2 class="section-title">Grille de Contrôle de Conformité</h2>
        <p class="section-description">Évaluez chaque critère pour déterminer la conformité administrative du rapport.</p>

        <form id="conformityCheckForm" action="/personnel/conformite/decision/<?= e($rapport_a_verifier['id']); ?>" method="POST">
            <div class="conformity-grid">
                <?php foreach ($criteres_conformite as $critere): ?>
                    <div class="conformity-item">
                        <label class="critere-label">
                            <input type="radio" name="critere_<?= e($critere['id']); ?>" value="conforme"
                                <?= ($criteres_evalues[$critere['id']] ?? '') === 'conforme' ? 'checked' : ''; ?> required>
                            Conforme
                        </label>
                        <label class="critere-label">
                            <input type="radio" name="critere_<?= e($critere['id']); ?>" value="non_conforme"
                                <?= ($criteres_evalues[$critere['id']] ?? '') === 'non_conforme' ? 'checked' : ''; ?> required>
                            Non Conforme
                        </label>
                        <span class="critere-text"><?= e($critere['libelle']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group mt-xl">
                <label for="decision_globale">Décision Générale :</label>
                <select id="decision_globale" name="decision_globale" required>
                    <option value="">Sélectionner une décision</option>
                    <option value="Conforme" <?= ($decision_globale_initiale === 'Conforme') ? 'selected' : ''; ?>>Conforme</option>
                    <option value="Non Conforme" <?= ($decision_globale_initiale === 'Non Conforme') ? 'selected' : ''; ?>>Non Conforme</option>
                </select>
            </div>

            <div class="form-group" id="commentaire_non_conforme_group" style="display:none;">
                <label for="commentaire_global">Commentaire / Justification (obligatoire si Non Conforme) :</label>
                <textarea id="commentaire_global" name="commentaire_global" rows="8" placeholder="Détaillez les raisons de non-conformité et les corrections attendues..."><?= e($commentaire_global_initial); ?></textarea>
                <small class="form-help">Ce commentaire sera transmis à l'étudiant.</small>
            </div>

            <div class="form-actions mt-xl">
                <button type="submit" class="btn btn-primary-green">
                    <span class="material-icons">send</span> Soumettre la Décision de Conformité
                </button>
                <a href="/personnel/conformite/rapports-a-verifier" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">cancel</span> Annuler
                </a>
            </div>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const decisionGlobaleSelect = document.getElementById('decision_globale');
        const commentaireNonConformeGroup = document.getElementById('commentaire_non_conforme_group');
        const commentaireGlobalTextarea = document.getElementById('commentaire_global');
        const conformityCheckForm = document.getElementById('conformityCheckForm');

        function toggleCommentField() {
            if (decisionGlobaleSelect.value === 'Non Conforme') {
                commentaireNonConformeGroup.style.display = 'flex';
                commentaireGlobalTextarea.setAttribute('required', 'required');
            } else {
                commentaireNonConformeGroup.style.display = 'none';
                commentaireGlobalTextarea.removeAttribute('required');
            }
        }

        if (decisionGlobaleSelect) {
            decisionGlobaleSelect.addEventListener('change', toggleCommentField);
            // Initialiser l'état au chargement de la page
            toggleCommentField();
        }

        if (conformityCheckForm) {
            conformityCheckForm.addEventListener('submit', function(event) {
                // Vérifier que tous les critères ont été évalués
                const allCriteriaRadios = conformityCheckForm.querySelectorAll('.conformity-grid input[type="radio"]');
                const evaluatedCriteria = new Set();
                allCriteriaRadios.forEach(radio => {
                    if (radio.checked) {
                        evaluatedCriteria.add(radio.name);
                    }
                });

                const expectedCriteriaCount = <?= count($criteres_conformite); ?>;
                if (evaluatedCriteria.size !== expectedCriteriaCount) {
                    alert('Veuillez évaluer tous les critères de conformité.');
                    event.preventDefault();
                    return;
                }

                // Vérifier le commentaire si Non Conforme
                if (decisionGlobaleSelect.value === 'Non Conforme') {
                    if (commentaireGlobalTextarea.value.trim() === '') {
                        alert('Un commentaire est obligatoire si la décision globale est "Non Conforme".');
                        event.preventDefault();
                        return;
                    }
                }
                console.log("Formulaire de décision de conformité soumis.");
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
    /* Styles spécifiques pour details_rapport_conformite.php */
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

    .section-description {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
    }

    /* Informations du rapport */
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

    .info-item span {
        color: var(--text-secondary);
    }

    /* Statut actuel du rapport */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-en-attente-de-vérification {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }
    .status-conforme {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }
    .status-non-conforme {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    /* Alertes (réutilisées) */
    .alert { /* Réutilisé des autres vues, y compris auth.css via style.css */
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-top: var(--spacing-lg);
        text-align: left;
        border: 1px solid;
        background-color: var(--primary-white);
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

    .btn-link { /* Réutilisé de suivi_rapport.php */
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: var(--font-weight-semibold);
        margin-left: var(--spacing-md);
        transition: color var(--transition-fast), text-decoration var(--transition-fast);
    }
    .btn-link:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }

    /* Contenu du rapport (réutilisé de details_rapport_commission.php) */
    .section-rapport-content {
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
        box-shadow: var(--shadow-sm);
    }

    .rapport-sections h3 {
        font-size: var(--font-size-lg);
        color: var(--primary-green-dark);
        margin-bottom: var(--spacing-md);
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px dashed var(--border-light);
    }

    .rapport-section-block {
        margin-bottom: var(--spacing-xl);
    }

    .rapport-section-block:last-child {
        margin-bottom: 0;
    }

    .section-content-display {
        font-size: var(--font-size-base);
        line-height: var(--line-height-normal);
        color: var(--text-primary);
    }

    .section-content-display p { margin-bottom: var(--spacing-sm); }
    .section-content-display strong { font-weight: var(--font-weight-bold); }
    .section-content-display em { font-style: italic; }
    .section-content-display ul, .section-content-display ol {
        margin-left: var(--spacing-lg);
        margin-bottom: var(--spacing-sm);
    }
    .section-content-display li { margin-bottom: var(--spacing-xs); }


    /* Grille de contrôle de conformité */
    .section-conformity-check .section-description {
        margin-bottom: var(--spacing-lg);
    }

    .conformity-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* 2 colonnes par défaut, adaptable */
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
        box-shadow: var(--shadow-sm);
    }

    .conformity-item {
        display: flex;
        flex-direction: column;
        padding: var(--spacing-sm);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-sm);
        background-color: var(--bg-secondary); /* Fond léger pour chaque critère */
    }

    .conformity-item .critere-label {
        display: flex;
        align-items: center;
        font-size: var(--font-size-sm);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        cursor: pointer;
    }

    .conformity-item input[type="radio"] {
        margin-right: var(--spacing-xs);
        transform: scale(1.1); /* Rendre les radios plus visibles */
    }

    .conformity-item .critere-text {
        font-weight: var(--font-weight-medium);
        color: var(--primary-blue-dark);
        margin-top: var(--spacing-sm);
        padding-top: var(--spacing-xs);
        border-top: 1px dotted var(--border-light);
    }

    /* Décision globale et commentaire */
    .form-group textarea {
        resize: vertical;
        min-height: 120px;
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


    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
    .ml-md { margin-left: var(--spacing-md); }
</style>