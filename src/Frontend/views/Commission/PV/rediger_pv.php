<?php
// src/Frontend/views/Commission/PV/rediger_pv.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le PV (si modification), la session, le rapport, les membres de la commission
// (proviennent du contrôleur PvController ou CommissionController).
//
//

$pv_a_rediger = $data['pv_a_rediger'] ?? null;
$is_edit_mode = (bool)$pv_a_rediger;

// Données fictives pour les listes déroulantes
$sessions_disponibles = $data['sessions_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'Session Juin 2025 - Vague 1'],
    ['id' => 2, 'libelle' => 'Session Juillet 2025 - Rattrapages'],
];

$rapports_disponibles = $data['rapports_disponibles'] ?? [
    ['id' => 1, 'titre' => 'Optimisation des Processus Logistiques par IA (Dupont Jean)', 'etudiant_id' => 1, 'session_id' => 1],
    ['id' => 2, 'titre' => 'Analyse de Données Financières (Curie Marie)', 'etudiant_id' => 2, 'session_id' => 1],
    ['id' => 3, 'titre' => 'Sécurité des Applications Web (Voltaire François)', 'etudiant_id' => 3, 'session_id' => 2],
];

$membres_commission_disponibles = $data['membres_commission_disponibles'] ?? [
    ['id' => 10, 'nom_complet' => 'Pr. Martin Sophie (Président)'],
    ['id' => 11, 'nom_complet' => 'Dr. Bernard Paul (Rapporteur)'],
    ['id' => 12, 'nom_complet' => 'Mme. Dubois Claire (Membre)'],
];

$decisions_finales = $data['decisions_finales'] ?? [
    ['code' => 'APPROUVE_ETAT', 'libelle' => 'Approuvé en l\'état'],
    ['code' => 'APPROUVE_RESERVE', 'libelle' => 'Approuvé sous réserve de corrections mineures'],
    ['code' => 'REFUSE', 'libelle' => 'Refusé'],
    ['code' => 'NECESSITE_DISCUSSION', 'libelle' => 'Nécessite discussion collégiale approfondie'],
];

?>

<div class="admin-module-container">
    <h1 class="admin-title"><?= $is_edit_mode ? 'Modifier le Procès-Verbal' : 'Rédiger un Nouveau Procès-Verbal'; ?></h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Détails du Procès-Verbal</h2>
        <form id="formRedactionPV" action="/commission/pv/<?= $is_edit_mode ? 'update/' . e($pv_a_rediger['id']) : 'create'; ?>" method="POST">

            <div class="form-group">
                <label for="session_id">Session de Validation :</label>
                <select id="session_id" name="session_id" required <?= $is_edit_mode ? 'disabled' : ''; ?>>
                    <option value="">Sélectionner une session</option>
                    <?php foreach ($sessions_disponibles as $session): ?>
                        <option value="<?= e($session['id']); ?>"
                            <?= ($pv_a_rediger['session_id'] ?? '') == $session['id'] ? 'selected' : ''; ?>>
                            <?= e($session['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($is_edit_mode): ?>
                    <input type="hidden" name="session_id" value="<?= e($pv_a_rediger['session_id']); ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="rapport_id">Rapport d'Étudiant :</label>
                <select id="rapport_id" name="rapport_id" required <?= $is_edit_mode ? 'disabled' : ''; ?>>
                    <option value="">Sélectionner un rapport</option>
                    <?php foreach ($rapports_disponibles as $rapport): ?>
                        <option value="<?= e($rapport['id']); ?>" data-session-id="<?= e($rapport['session_id']); ?>"
                            <?= ($pv_a_rediger['rapport_id'] ?? '') == $rapport['id'] ? 'selected' : ''; ?>>
                            <?= e($rapport['titre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($is_edit_mode): ?>
                    <input type="hidden" name="rapport_id" value="<?= e($pv_a_rediger['rapport_id']); ?>">
                <?php endif; ?>
                <small class="form-help">Seuls les rapports de la session sélectionnée sont affichés.</small>
            </div>

            <div class="form-group">
                <label for="president_id">Président de la Commission :</label>
                <select id="president_id" name="president_id" required>
                    <option value="">Sélectionner le président</option>
                    <?php foreach ($membres_commission_disponibles as $membre): ?>
                        <option value="<?= e($membre['id']); ?>"
                            <?= ($pv_a_rediger['president_id'] ?? '') == $membre['id'] ? 'selected' : ''; ?>>
                            <?= e($membre['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="rapporteur_id">Rapporteur :</label>
                <select id="rapporteur_id" name="rapporteur_id" required>
                    <option value="">Sélectionner le rapporteur</option>
                    <?php foreach ($membres_commission_disponibles as $membre): ?>
                        <option value="<?= e($membre['id']); ?>"
                            <?= ($pv_a_rediger['rapporteur_id'] ?? '') == $membre['id'] ? 'selected' : ''; ?>>
                            <?= e($membre['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="decision_finale_code">Décision Finale :</label>
                <select id="decision_finale_code" name="decision_finale_code" required>
                    <option value="">Sélectionner une décision</option>
                    <?php foreach ($decisions_finales as $decision): ?>
                        <option value="<?= e($decision['code']); ?>"
                            <?= ($pv_a_rediger['decision_finale_code'] ?? '') == $decision['code'] ? 'selected' : ''; ?>>
                            <?= e($decision['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="note_attribuee">Note Attribuée (sur 20) :</label>
                <input type="number" id="note_attribuee" name="note_attribuee" min="0" max="20" step="0.01"
                       value="<?= e($pv_a_rediger['note_attribuee'] ?? ''); ?>" required placeholder="Ex: 15.50">
            </div>

            <div class="form-group">
                <label for="contenu_pv">Contenu du Procès-Verbal :</label>
                <textarea id="contenu_pv" name="contenu_pv" rows="15" class="wysiwyg-editor" required placeholder="Rédigez ici le compte-rendu détaillé de la soutenance, les observations, arguments et recommandations..."><?= e($pv_a_rediger['contenu'] ?? ''); ?></textarea>
                <small class="form-help">Utilisez un éditeur de texte enrichi (WYSIWYG) pour la mise en forme.</small>
            </div>

            <div class="form-actions mt-xl">
                <button type="submit" class="btn btn-primary-blue">
                    <span class="material-icons"><?= $is_edit_mode ? 'save' : 'post_add'; ?></span>
                    <?= $is_edit_mode ? 'Enregistrer le Brouillon' : 'Enregistrer le Brouillon'; ?>
                </button>
                <?php if ($is_edit_mode): // Option "Soumettre pour validation" uniquement en mode édition d'un brouillon existant ?>
                    <button type="button" class="btn btn-primary-green ml-md" id="soumettrePourValidationBtn">
                        <span class="material-icons">send</span> Soumettre pour Validation
                    </button>
                <?php endif; ?>
                <a href="/commission/pv/liste" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">cancel</span> Annuler
                </a>
            </div>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique de filtrage des rapports par session
        const sessionIdSelect = document.getElementById('session_id');
        const rapportIdSelect = document.getElementById('rapport_id');
        const allRapports = Array.from(rapportIdSelect.options); // Garder toutes les options originales

        function filterRapportsBySession() {
            const selectedSessionId = sessionIdSelect.value;
            rapportIdSelect.innerHTML = '<option value="">Sélectionner un rapport</option>'; // Réinitialiser

            allRapports.forEach(option => {
                if (option.value === "" || option.dataset.sessionId == selectedSessionId) {
                    rapportIdSelect.appendChild(option);
                }
            });

            // Pré-sélectionner si en mode édition et le rapport correspond à la session
            const currentRapportId = "<?= e($pv_a_rediger['rapport_id'] ?? ''); ?>";
            if (currentRapportId && allRapports.some(opt => opt.value == currentRapportId && opt.dataset.sessionId == selectedSessionId)) {
                rapportIdSelect.value = currentRapportId;
            } else {
                rapportIdSelect.value = ""; // Réinitialiser si le rapport n'est pas dans la nouvelle session filtrée
            }
        }

        if (sessionIdSelect && rapportIdSelect) {
            sessionIdSelect.addEventListener('change', filterRapportsBySession);
            // Appliquer le filtre au chargement si une session est déjà sélectionnée
            filterRapportsBySession();
        }


        // Initialisation d'un éditeur WYSIWYG (placeholder)
        // Vous devrez inclure une bibliothèque WYSIWYG (ex: TinyMCE, CKEditor) dans app.php
        // ou une autre partie de votre layout.
        // if (typeof ClassicEditor !== 'undefined') { // Exemple pour CKEditor 5
        //     ClassicEditor
        //         .create(document.querySelector('#contenu_pv'))
        //         .catch(error => {
        //             console.error('Erreur lors de l\'initialisation de l\'éditeur WYSIWYG', error);
        //         });
        // } else {
        //     console.warn('WYSIWYG editor library (e.g., CKEditor) not found. Textarea will be plain.');
        // }

        const form = document.getElementById('formRedactionPV');
        if (form) {
            form.addEventListener('submit', function(event) {
                const sessionId = document.getElementById('session_id').value;
                const rapportId = document.getElementById('rapport_id').value;
                const presidentId = document.getElementById('president_id').value;
                const rapporteurId = document.getElementById('rapporteur_id').value;
                const decisionFinale = document.getElementById('decision_finale_code').value;
                const noteAttribuee = document.getElementById('note_attribuee').value;
                // const contenuPv = editor.getData(); // Si WYSIWYG, récupérer le contenu de l'éditeur

                if (!sessionId || !rapportId || !presidentId || !rapporteurId || !decisionFinale || !noteAttribuee) {
                    alert('Veuillez remplir tous les champs obligatoires.');
                    event.preventDefault();
                    return;
                }
                if (parseFloat(noteAttribuee) < 0 || parseFloat(noteAttribuee) > 20) {
                    alert('La note doit être comprise entre 0 et 20.');
                    event.preventDefault();
                    return;
                }
                // if (contenuPv.trim() === '') {
                //     alert('Le contenu du procès-verbal ne peut pas être vide.');
                //     event.preventDefault();
                //     return;
                // }

                console.log("Formulaire de rédaction PV soumis comme brouillon.");
            });
        }

        // Logique pour le bouton "Soumettre pour Validation"
        const soumettrePourValidationBtn = document.getElementById('soumettrePourValidationBtn');
        if (soumettrePourValidationBtn) {
            soumettrePourValidationBtn.addEventListener('click', function() {
                if (confirm('Êtes-vous sûr de vouloir soumettre ce PV pour validation par la commission ? Cette action est irréversible pour le statut actuel.')) {
                    // Créer un input caché pour indiquer l'action de soumission
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'action_type';
                    hiddenInput.value = 'submit_for_validation';
                    form.appendChild(hiddenInput);

                    // Soumettre le formulaire
                    form.submit();
                }
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
    /* Styles spécifiques pour rediger_pv.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1000px;
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
        min-height: 250px; /* Grande taille pour l'éditeur de PV */
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    /* Boutons - réutilisation des styles existants */
    .form-actions {
        display: flex;
        justify-content: flex-end; /* Aligner les boutons à droite */
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

    .btn-primary-blue {
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
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
</style>