<?php
// src/Frontend/views/Commission/Rapports/details_rapport_commission.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le rapport (proviennent du contrôleur ValidationRapportController ou RapportController)
// Ces données sont des exemples pour structurer la vue.
//
//
//

$rapport_details = $data['rapport_details'] ?? [
    'id' => 1,
    'numero_rapport' => 'RAP-2025-0045',
    'titre' => 'Optimisation des Processus Logistiques par l\'Intégration de l\'IA',
    'theme' => 'Intelligence Artificielle en Logistique',
    'etudiant_nom_complet' => 'Dupont Jean (ETU-2025-0001)',
    'date_soumission' => '2025-06-28 09:00:00',
    'status_conformite' => 'Conforme', // Conforme, Non Conforme
    'commentaires_conformite' => [], // Sera rempli si non conforme
    'status_validation_commission' => 'En attente d\'évaluation', // En attente, Approuvé, Refusé, Approuvé sous réserve
    'sections' => [
        ['titre' => 'Introduction', 'contenu' => '<p>Ce rapport explore l\'application de l\'intelligence artificielle...</p><p>Les objectifs sont de...</p>'],
        ['titre' => 'Développement et Méthodologie', 'contenu' => '<p>Nous avons utilisé une approche hybride...</p><p>Les données ont été collectées via...</p>'],
        ['titre' => 'Analyse des Résultats', 'contenu' => '<p>Les performances du modèle IA ont dépassé les attentes sur X critères...</p><p>Les défis rencontrés incluent...</p>'],
        ['titre' => 'Conclusion', 'contenu' => '<p>En conclusion, l\'intégration de l\'IA dans la logistique offre des perspectives prometteuses...</p>'],
        ['titre' => 'Bibliographie', 'contenu' => '<p>[1] Article A, Année. Titre.</p><p>[2] Livre B, Année. Titre.</p>'],
        ['titre' => 'Annexes', 'contenu' => '<p>Diagrammes UML, extraits de code, etc.</p>', 'optional' => true],
    ],
];

// Exemple de commentaires de conformité si le rapport était non conforme
if ($rapport_details['status_conformite'] === 'Non Conforme' && empty($rapport_details['commentaires_conformite'])) {
    $rapport_details['commentaires_conformite'] = [
        ['agent' => 'Agent Conformité 001', 'date' => '2025-06-29 10:00:00', 'commentaire' => 'La bibliographie n\'est pas formatée selon les normes APA. Veuillez corriger.'],
        ['agent' => 'Agent Conformité 001', 'date' => '2025-06-29 10:00:00', 'commentaire' => 'Absence du résumé en début de rapport.'],
    ];
}
?>

<div class="admin-module-container">
    <h1 class="admin-title">Détails du Rapport de Soutenance</h1>

    <section class="section-rapport-overview admin-card">
        <div class="section-header">
            <h2 class="section-title">Rapport : <?= e($rapport_details['titre']); ?></h2>
            <div class="rapport-actions">
                <a href="/commission/rapports/vote/<?= e($rapport_details['id']); ?>" class="btn btn-primary-blue">
                    <span class="material-icons">how_to_vote</span> Évaluer ce Rapport
                </a>
                <a href="/commission/rapports/liste" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">arrow_back</span> Retour à la Liste
                </a>
            </div>
        </div>

        <div class="rapport-metadata-grid">
            <div class="meta-item">
                <strong>Numéro Rapport :</strong> <span><?= e($rapport_details['numero_rapport']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Étudiant :</strong> <span><?= e($rapport_details['etudiant_nom_complet']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Thème :</strong> <span><?= e($rapport_details['theme']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Date Soumission :</strong> <span><?= e(date('d/m/Y H:i', strtotime($rapport_details['date_soumission']))); ?></span>
            </div>
            <div class="meta-item">
                <strong>Statut Conformité :</strong>
                <span class="status-indicator status-<?= e(strtolower(str_replace(' ', '-', $rapport_details['status_conformite']))); ?>">
                    <?= e($rapport_details['status_conformite']); ?>
                </span>
            </div>
            <div class="meta-item">
                <strong>Statut Commission :</strong>
                <span class="status-indicator status-<?= e(strtolower(str_replace(' ', '-', $rapport_details['status_validation_commission']))); ?>">
                    <?= e($rapport_details['status_validation_commission']); ?>
                </span>
            </div>
        </div>

        <?php if ($rapport_details['status_conformite'] === 'Non Conforme' && !empty($rapport_details['commentaires_conformite'])): ?>
            <div class="alert alert-warning mt-xl">
                <span class="material-icons">warning_amber</span>
                Ce rapport a été précédemment jugé non conforme. L'étudiant a dû apporter des corrections.
                <div class="comments-list mt-sm">
                    <strong>Commentaires de conformité :</strong>
                    <ul>
                        <?php foreach ($rapport_details['commentaires_conformite'] as $comment): ?>
                            <li>[<?= e(date('d/m/Y', strtotime($comment['date']))); ?> par <?= e($comment['agent']); ?>] <?= e($comment['commentaire']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-rapport-content admin-card mt-xl">
        <h2 class="section-title">Contenu Détaillé du Rapport</h2>
        <div class="rapport-sections">
            <?php foreach ($rapport_details['sections'] as $section): ?>
                <div class="rapport-section-block">
                    <h3><?= e($section['titre']); ?></h3>
                    <div class="section-content-display">
                        <?= $section['contenu']; // Le contenu HTML est déjà échappé ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript spécifique à cette vue si nécessaire.
        // Par exemple, pour une navigation par ancre si les sections sont très longues,
        // ou pour un mode "lecture" qui masque les actions.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour details_rapport_commission.php */
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

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        margin: 0;
    }

    /* Actions du rapport */
    .rapport-actions {
        display: flex;
        gap: var(--spacing-sm);
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

    /* Métadonnées du rapport */
    .rapport-metadata-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
        box-shadow: var(--shadow-sm);
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

    /* Statuts spécifiques (réutilisés) */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }

    .status-conforme {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-non-conforme {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .status-en-attente-d-évaluation, .status-en-attente {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-approuvé {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-refusé {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .status-approuvé-sous-réserve {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    /* Alertes (réutilisées) */
    .alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: flex-start; /* Aligne l'icône au début du texte */
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-md);
        text-align: left;
        border: 1px solid;
        background-color: var(--bg-primary); /* Fond blanc pour les alertes dans le contenu */
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

    .comments-list {
        margin-top: var(--spacing-md);
        padding-left: var(--spacing-md);
        border-left: 3px solid var(--border-medium);
    }
    .comments-list ul {
        list-style: none;
        padding: 0;
    }
    .comments-list li {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
    }
    .comments-list strong {
        color: var(--text-primary);
    }


    /* Contenu détaillé du rapport */
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
        margin-bottom: var(--spacing-xl); /* Espace entre les grandes sections */
    }

    .rapport-section-block:last-child {
        margin-bottom: 0;
    }

    .section-content-display {
        font-size: var(--font-size-base);
        line-height: var(--line-height-normal);
        color: var(--text-primary);
    }

    /* Styles pour le contenu HTML dans les sections (si WYSIWYG) */
    .section-content-display p { margin-bottom: var(--spacing-sm); }
    .section-content-display strong { font-weight: var(--font-weight-bold); }
    .section-content-display em { font-style: italic; }
    .section-content-display ul, .section-content-display ol {
        margin-left: var(--spacing-lg);
        margin-bottom: var(--spacing-sm);
    }
    .section-content-display li { margin-bottom: var(--spacing-xs); }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
    .mt-sm { margin-top: var(--spacing-sm); }
</style>