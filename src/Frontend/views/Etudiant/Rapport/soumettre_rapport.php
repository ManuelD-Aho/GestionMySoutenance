<?php
// src/Frontend/views/Etudiant/Rapport/soumettre_rapport.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour l'étudiant, les prérequis, le rapport existant (brouillon) et les modèles
// (proviennent du RapportController ou EtudiantDashboardController).
//
//
//

$student_eligibility = $data['student_eligibility'] ?? [
    'is_eligible' => true,
    'penalties_details' => null, // Ex: ['montant' => 5000, 'type' => 'FINANCIERE']
    'reason' => '',
];

$rapport_brouillon = $data['rapport_brouillon'] ?? [
    'id' => null,
    'titre' => '',
    'theme' => '',
    'nb_pages_estime' => null,
    'contenu_introduction' => '',
    'contenu_developpement' => '',
    'contenu_analyse' => '',
    'contenu_conclusion' => '',
    'contenu_bibliographie' => '',
    'selected_template_id' => null,
    'last_saved_at' => null,
];

$modeles_preformates = $data['modeles_preformates'] ?? [
    ['id' => 1, 'nom' => 'Modèle Standard Master 2', 'description' => 'Structure classique pour les rapports M2.'],
    ['id' => 2, 'nom' => 'Modèle Recherche Approfondie', 'description' => 'Avec sections détaillées pour la méthodologie de recherche.'],
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Soumettre votre Rapport de Soutenance</h1>

    <?php if (!$student_eligibility['is_eligible']): ?>
        <div class="alert alert-error">
            <span class="material-icons">block</span>
            <strong>Soumission bloquée !</strong>
            <p><?= e($student_eligibility['reason']); ?></p>
            <?php if ($student_eligibility['penalties_details']): ?>
                <p>Veuillez régulariser la pénalité de <strong class="text-red"><?= e($student_eligibility['penalties_details']['montant']); ?> FCFA (<?= e($student_eligibility['penalties_details']['type']); ?>)</strong> auprès du Responsable Scolarité.</p>
                <a href="/etudiant/reclamations/new?type=penalite" class="link-secondary mt-sm">Contacter le Responsable Scolarité</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="section-report-submission admin-card <?= !$student_eligibility['is_eligible'] ? 'disabled-section' : ''; ?>">
        <h2 class="section-title">Préparer et Rédiger votre Rapport</h2>

        <form id="formSoumettreRapport" action="/etudiant/rapport/soumettre" method="POST" class="<?= !$student_eligibility['is_eligible'] ? 'disabled-form' : ''; ?>">

            <div class="form-group">
                <label for="template_selection">Choisir un Modèle ou Page Blanche :</label>
                <select id="template_selection" name="template_id" <?= $rapport_brouillon['id'] ? 'disabled' : ''; ?>>
                    <option value="">Page Blanche (Liberté totale)</option>
                    <?php foreach ($modeles_preformates as $template): ?>
                        <option value="<?= e($template['id']); ?>"
                            <?= ($rapport_brouillon['selected_template_id'] ?? '') == $template['id'] ? 'selected' : ''; ?>>
                            <?= e($template['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Si vous avez déjà un brouillon, le choix du modèle est désactivé. Chargez le modèle pour un nouveau rapport.</small>
            </div>

            <fieldset class="form-section">
                <legend>Métadonnées du Rapport</legend>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="titre">Titre Précis du Rapport :</label>
                        <input type="text" id="titre" name="titre" value="<?= e($rapport_brouillon['titre']); ?>" required placeholder="Titre complet de votre travail">
                    </div>
                    <div class="form-group">
                        <label for="theme">Thème Principal :</label>
                        <input type="text" id="theme" name="theme" value="<?= e($rapport_brouillon['theme']); ?>" required placeholder="Mots-clés ou thème général">
                    </div>
                    <div class="form-group">
                        <label for="nb_pages_estime">Estimation du Nombre de Pages :</label>
                        <input type="number" id="nb_pages_estime" name="nb_pages_estime" min="1" value="<?= e($rapport_brouillon['nb_pages_estime']); ?>" placeholder="Ex: 50">
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section mt-xl">
                <legend>Contenu du Rapport</legend>
                <div class="form-group">
                    <label for="contenu_introduction">Introduction :</label>
                    <textarea id="contenu_introduction" name="sections[introduction]" rows="10" class="wysiwyg-editor" required><?= e($rapport_brouillon['contenu_introduction']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="contenu_developpement">Développement et Analyse :</label>
                    <textarea id="contenu_developpement" name="sections[developpement]" rows="20" class="wysiwyg-editor" required><?= e($rapport_brouillon['contenu_developpement']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="contenu_conclusion">Conclusion :</label>
                    <textarea id="contenu_conclusion" name="sections[conclusion]" rows="10" class="wysiwyg-editor" required><?= e($rapport_brouillon['contenu_conclusion']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="contenu_bibliographie">Bibliographie :</label>
                    <textarea id="contenu_bibliographie" name="sections[bibliographie]" rows="10" class="wysiwyg-editor" required><?= e($rapport_brouillon['contenu_bibliographie']); ?></textarea>
                </div>
            </fieldset>

            <div class="form-actions mt-xl">
                <button type="submit" name="action" value="save_draft" class="btn btn-secondary-gray" id="saveDraftBtn">
                    <span class="material-icons">save</span> Enregistrer le Brouillon
                    <?php if ($rapport_brouillon['last_saved_at']): ?>
                        <span class="last-saved-text">(Dernière sauvegarde: <?= e(date('H:i', strtotime($rapport_brouillon['last_saved_at']))); ?>)</span>
                    <?php endif; ?>
                </button>
                <button type="submit" name="action" value="submit_report" class="btn btn-primary-green ml-md" id="submitReportBtn" <?= !$student_eligibility['is_eligible'] ? 'disabled' : ''; ?>>
                    <span class="material-icons">send</span> Soumettre le Rapport Final
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des éditeurs WYSIWYG
        // Assurez-vous que la bibliothèque WYSIWYG est chargée dans app.php (ex: CKEditor 5)
        const editorInstances = {};
        document.querySelectorAll('.wysiwyg-editor').forEach(textarea => {
            // C'est un placeholder. En production, vous feriez :
            // ClassicEditor.create(textarea)
            //    .then(editor => { editorInstances[textarea.id] = editor; })
            //    .catch(error => console.error(error));
            // Pour l'instant, on utilise juste les textareas.
            editorInstances[textarea.id] = textarea; // Simuler l'instance d'éditeur
        });

        const form = document.getElementById('formSoumettreRapport');
        const templateSelect = document.getElementById('template_selection');
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        const submitReportBtn = document.getElementById('submitReportBtn');
        const isEligible = <?= json_encode($student_eligibility['is_eligible']); ?>;

        // Désactiver le formulaire si l'étudiant n'est pas éligible
        if (!isEligible) {
            form.querySelectorAll('input, select, textarea, button').forEach(el => {
                if (el.id !== 'submitReportBtn') el.disabled = true; // Garder le bouton de soumission désactivé mais les autres non si on veut pouvoir modifier le rapport même si non éligible (pour régulariser après)
            });
            // submitReportBtn est déjà désactivé par PHP
        }


        // Fonction pour charger le contenu d'un modèle (AJAX)
        if (templateSelect && !<?= json_encode((bool)$rapport_brouillon['id']); ?>) { // Active si pas de brouillon existant
            templateSelect.addEventListener('change', function() {
                const templateId = this.value;
                if (templateId) {
                    if (confirm('Charger ce modèle ? Le contenu actuel des sections sera remplacé.')) {
                        fetch(`/api/rapport/load-template/${templateId}`) // Assurez-vous que cette route API existe
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.template_content) {
                                    document.getElementById('titre').value = data.template_content.titre || '';
                                    document.getElementById('theme').value = data.template_content.theme || '';
                                    document.getElementById('nb_pages_estime').value = data.template_content.nb_pages_estime || '';

                                    // Pour chaque section, mettre à jour le contenu
                                    for (const sectionName in data.template_content.sections) {
                                        const editor = editorInstances[`contenu_${sectionName}`];
                                        if (editor) {
                                            // Si CKEditor: editor.setData(data.template_content.sections[sectionName]);
                                            editor.value = data.template_content.sections[sectionName];
                                        }
                                    }
                                    alert('Modèle chargé avec succès !');
                                } else {
                                    alert('Erreur lors du chargement du modèle: ' + (data.message || ''));
                                }
                            })
                            .catch(error => {
                                console.error('Erreur AJAX chargement modèle:', error);
                                alert('Erreur de communication lors du chargement du modèle.');
                            });
                    } else {
                        templateSelect.value = ''; // Réinitialiser la sélection si l'utilisateur annule
                    }
                }
            });
        }


        form.addEventListener('submit', function(event) {
            // Validation des champs métadonnées
            const titre = document.getElementById('titre').value.trim();
            const theme = document.getElementById('theme').value.trim();
            if (!titre || !theme) {
                alert('Le titre et le thème du rapport sont obligatoires.');
                event.preventDefault();
                return;
            }

            // Validation des sections de contenu (si WYSIWYG, récupérer le contenu de l'éditeur)
            const sectionsToCheck = ['contenu_introduction', 'contenu_developpement', 'contenu_conclusion', 'contenu_bibliographie'];
            for (const sectionId of sectionsToCheck) {
                const editor = editorInstances[sectionId];
                // const content = editor.getData(); // Pour CKEditor
                const content = editor.value.trim(); // Pour simple textarea
                if (!content) {
                    alert(`La section "${document.querySelector('label[for="' + sectionId + '"]').textContent}" est obligatoire.`);
                    event.preventDefault();
                    return;
                }
            }

            const submitAction = event.submitter.value; // Bouton qui a déclenché la soumission

            if (submitAction === 'submit_report') {
                if (!isEligible) {
                    alert("Vous ne pouvez pas soumettre le rapport tant que votre situation n'est pas régularisée.");
                    event.preventDefault();
                    return;
                }
                if (!confirm('Êtes-vous sûr de vouloir soumettre DÉFINITIVEMENT votre rapport ? Cette action est irréversible.')) {
                    event.preventDefault();
                    return;
                }
            } else if (submitAction === 'save_draft') {
                // Pas de validation stricte pour le brouillon, juste s'assurer que ça s'envoie
                console.log("Enregistrement du brouillon...");
            }

            // Le formulaire sera soumis via la méthode POST classique
        });

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour soumettre_rapport.php */
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

    /* Alertes spécifiques (ex: pénalités) */
    .alert { /* Réutilisé des autres vues, y compris auth.css via style.css */
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
        background-color: var(--bg-primary); /* Fond blanc pour les alertes dans le contenu */
    }

    .alert-error {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
        border-color: var(--accent-red-dark);
    }

    .alert .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    /* Formulaire de soumission de rapport */
    .section-report-submission.disabled-section {
        opacity: 0.7;
        pointer-events: none; /* Désactive les interactions */
        filter: grayscale(80%); /* Rend la section grisée */
    }

    .disabled-form input,
    .disabled-form select,
    .disabled-form textarea {
        cursor: not-allowed !important;
    }

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

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    .form-group textarea.wysiwyg-editor {
        min-height: 250px;
    }

    /* Fieldset et legend pour structurer le formulaire */
    fieldset.form-section {
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        background-color: var(--primary-white);
    }

    fieldset.form-section legend {
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        font-weight: var(--font-weight-semibold);
        padding: 0 var(--spacing-xs);
        margin-left: var(--spacing-sm);
    }

    .form-grid { /* Pour les métadonnées */
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-md);
    }

    /* Actions du formulaire (boutons) */
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

    .btn-primary-green:hover:not(:disabled) {
        background-color: var(--primary-green-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary-gray {
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }

    .btn-secondary-gray:hover:not(:disabled) {
        background-color: var(--border-medium);
        box-shadow: var(--shadow-sm);
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .ml-md { margin-left: var(--spacing-md); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-sm { margin-top: var(--spacing-sm); }


    .last-saved-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-left: var(--spacing-md);
        font-style: italic;
    }