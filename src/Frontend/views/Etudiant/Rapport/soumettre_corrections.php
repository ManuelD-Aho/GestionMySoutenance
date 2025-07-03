<?php
// src/Frontend/views/Etudiant/Rapport/soumettre_corrections.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le rapport à corriger (proviennent du RapportController)
//
//

$rapport_a_corriger = $data['rapport_a_corriger'] ?? [
    'id' => 1,
    'numero_rapport' => 'RAP-2025-0045',
    'titre' => 'Optimisation des Processus Logistiques par l\'Intégration de l\'IA',
    'contenu_introduction' => '<p>Ancienne introduction...</p>',
    'contenu_developpement' => '<p>Ancien développement...</p>',
    'contenu_conclusion' => '<p>Ancienne conclusion...</p>',
    'commentaires_precedents' => [
        ['type' => 'conformite', 'agent' => 'Agent Conformité 001', 'date' => '2025-06-29', 'commentaire' => 'La bibliographie n\'est pas formatée selon les normes APA. Veuillez corriger.'],
        ['type' => 'commission', 'agent' => 'Pr. Martin Sophie', 'date' => '2025-07-01', 'commentaire' => 'La section "Analyse des Résultats" manque de détails sur les hypothèses initiales.'],
    ],
    'status_conformite' => 'Non Conforme', // ou 'Approuvé sous réserve'
    'derniere_soumission_date' => '2025-06-28',
];

$commentaires_visibles = [];
foreach ($rapport_a_corriger['commentaires_precedents'] as $comment) {
    $commentaires_visibles[] = [
        'source' => $comment['type'] === 'conformite' ? 'Conformité' : 'Commission',
        'agent' => $comment['agent'],
        'date' => $comment['date'],
        'commentaire' => $comment['commentaire'],
    ];
}

?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Soumettre les Corrections du Rapport</h1>

    <section class="section-rapport-info admin-card">
        <h2 class="section-title">Rapport Concerné : <?= e($rapport_a_corriger['titre']); ?> (<?= e($rapport_a_corriger['numero_rapport']); ?>)</h2>
        <p class="section-description">Veuillez apporter les corrections demandées et soumettre à nouveau votre rapport.</p>

        <?php if (!empty($commentaires_visibles)): ?>
            <div class="alert alert-warning mt-lg">
                <span class="material-icons">warning_amber</span>
                <div class="comments-list-section">
                    <strong>Commentaires et Corrections demandées :</strong>
                    <ul class="comments-for-correction">
                        <?php foreach ($commentaires_visibles as $comment): ?>
                            <li>
                                <strong>De <?= e($comment['agent']); ?> (<?= e($comment['source']); ?>) le <?= e(date('d/m/Y', strtotime($comment['date']))); ?> :</strong>
                                <p><?= e($comment['commentaire']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-form-corrections admin-card mt-xl">
        <h2 class="section-title">Édition du Contenu du Rapport</h2>
        <form id="formSoumettreCorrections" action="/etudiant/rapport/soumettre-corrections/<?= e($rapport_a_corriger['id']); ?>" method="POST">

            <div class="form-group">
                <label for="contenu_introduction">Introduction :</label>
                <textarea id="contenu_introduction" name="sections[introduction]" rows="8" class="wysiwyg-editor"><?= e($rapport_a_corriger['contenu_introduction'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="contenu_developpement">Développement :</label>
                <textarea id="contenu_developpement" name="sections[developpement]" rows="15" class="wysiwyg-editor"><?= e($rapport_a_corriger['contenu_developpement'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="contenu_conclusion">Conclusion :</label>
                <textarea id="contenu_conclusion" name="sections[conclusion]" rows="8" class="wysiwyg-editor"><?= e($rapport_a_corriger['contenu_conclusion'] ?? ''); ?></textarea>
            </div>
            <div class="form-group mt-xl">
                <label for="note_explicative">Note Explicative des Corrections (Obligatoire) :</label>
                <textarea id="note_explicative" name="note_explicative" rows="5" required placeholder="Veuillez résumer les corrections que vous avez apportées en réponse aux commentaires."></textarea>
                <small class="form-help">Cette note est cruciale pour le réexamen de votre rapport.</small>
            </div>

            <div class="form-actions mt-xl">
                <button type="submit" class="btn btn-primary-blue">
                    <span class="material-icons">send</span> Re-soumettre le Rapport
                </button>
                <a href="/etudiant/rapport/suivi" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">cancel</span> Annuler
                </a>
            </div>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de l'éditeur WYSIWYG pour chaque textarea
        // Assurez-vous que la bibliothèque WYSIWYG est chargée dans app.php
        // Exemple avec CKEditor 5 (si utilisé)
        // document.querySelectorAll('.wysiwyg-editor').forEach(textarea => {
        //     ClassicEditor
        //         .create(textarea)
        //         .catch(error => {
        //             console.error('Erreur lors de l\'initialisation de l\'éditeur WYSIWYG', error);
        //         });
        // });

        const form = document.getElementById('formSoumettreCorrections');
        if (form) {
            form.addEventListener('submit', function(event) {
                const noteExplicative = document.getElementById('note_explicative').value.trim();
                // Récupérer le contenu de l'éditeur WYSIWYG si utilisé
                // const contenuIntroduction = ClassicEditor.instances.get('contenu_introduction').getData();

                if (!noteExplicative) {
                    alert('La note explicative des corrections est obligatoire.');
                    event.preventDefault();
                    return;
                }

                // D'autres validations peuvent être ajoutées ici (ex: contenu des sections non vides)

                console.log("Formulaire de soumission de corrections soumis.");
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
    /* Styles spécifiques pour soumettre_corrections.php */
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

    /* Alertes pour les commentaires précédents (réutilisées) */
    .alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-md);
        text-align: left;
        border: 1px solid;
        background-color: var(--primary-white); /* Fond blanc pour l'alerte */
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

    .comments-list-section {
        flex-grow: 1; /* Permet au contenu de prendre l'espace */
    }

    .comments-list-section strong {
        display: block;
        margin-bottom: var(--spacing-xs);
        color: var(--text-primary);
    }

    .comments-for-correction {
        list-style: none;
        padding: 0;
        margin-left: var(--spacing-md); /* Indentation pour les points */
        border-left: 3px solid var(--border-medium);
        padding-left: var(--spacing-md);
    }
    .comments-for-correction li {
        margin-bottom: var(--spacing-sm);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    .comments-for-correction li p {
        margin-top: var(--spacing-xs);
        font-style: italic;
        color: var(--text-primary);
    }


    /* Formulaire de corrections */
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

    .form-group textarea {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
        resize: vertical;
        min-height: 120px; /* Taille pour les sections de rapport */
    }

    .form-group textarea.wysiwyg-editor {
        min-height: 200px; /* Plus grande taille pour l'éditeur WYSIWYG */
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
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>