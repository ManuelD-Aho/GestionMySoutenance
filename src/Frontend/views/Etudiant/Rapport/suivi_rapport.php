<?php
// src/Frontend/views/Etudiant/Rapport/suivi_rapport.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le suivi du rapport (proviennent du RapportController)
//
//

$rapport_suivi = $data['rapport_suivi'] ?? [
    'id' => 1,
    'numero_rapport' => 'RAP-2025-0045',
    'titre' => 'Optimisation des Processus Logistiques par l\'Intégration de l\'IA',
    'date_soumission' => '2025-06-28 09:00:00',
    'statut_actuel' => 'En évaluation par la commission', // Soumis, En contrôle conformité, Non conforme, En évaluation par la commission, Approuvé, Approuvé sous réserve, Refusé
    'lien_soumettre_corrections' => '/etudiant/rapport/soumettre-corrections/1', // Lien vers la vue de corrections
    'lien_pv_final' => '/etudiant/documents/pv/1', // Lien vers le PV final
    'workflow_historique' => [
        ['etape' => 'Rapport Soumis', 'date' => '2025-06-28 09:00:00', 'statut' => 'Terminé', 'details' => 'Soumission initiale du rapport.'],
        ['etape' => 'Contrôle de Conformité', 'date' => '2025-06-29 10:30:00', 'statut' => 'Terminé', 'details' => 'Vérification terminée. Résultat : Conforme.'],
        ['etape' => 'Évaluation par la Commission', 'date' => '2025-06-29 11:00:00', 'statut' => 'En cours', 'details' => 'Le rapport est en cours d\'évaluation par la commission.'],
        // Exemple d'étape avec problème
        // ['etape' => 'Contrôle de Conformité', 'date' => '2025-06-29 10:30:00', 'statut' => 'Non Conforme', 'details' => 'Manque de formatage de la bibliographie. Voir les commentaires détaillés.', 'commentaires' => [['agent' => 'Agent Conformité 001', 'texte' => 'Problème de formatage.']]],
        // ['etape' => 'Attente Corrections Étudiant', 'date' => '2025-06-29 10:35:00', 'statut' => 'En attente', 'details' => 'Rapport retourné pour corrections.'],
    ],
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Suivi de mon Rapport de Soutenance</h1>

    <section class="section-rapport-info admin-card">
        <h2 class="section-title">Rapport : <?= e($rapport_suivi['titre']); ?> (<?= e($rapport_suivi['numero_rapport']); ?>)</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Date de Soumission :</strong> <span><?= e(date('d/m/Y H:i', strtotime($rapport_suivi['date_soumission']))); ?></span>
            </div>
            <div class="info-item">
                <strong>Statut Actuel :</strong>
                <span class="status-indicator status-<?= strtolower(str_replace(' ', '-', e($rapport_suivi['statut_actuel']))); ?>">
                    <?= e($rapport_suivi['statut_actuel']); ?>
                </span>
            </div>
        </div>

        <?php if ($rapport_suivi['statut_actuel'] === 'Non conforme' || $rapport_suivi['statut_actuel'] === 'Approuvé sous réserve' || $rapport_suivi['statut_actuel'] === 'Refusé'): ?>
            <div class="alert alert-warning mt-lg">
                <span class="material-icons">info</span>
                Des actions sont requises pour votre rapport.
                <a href="<?= e($rapport_suivi['lien_soumettre_corrections'] ?? '#'); ?>" class="btn-link ml-md">Soumettre les corrections</a>
            </div>
        <?php elseif ($rapport_suivi['statut_actuel'] === 'Approuvé' && $rapport_suivi['lien_pv_final']): ?>
            <div class="alert alert-success mt-lg">
                <span class="material-icons">check_circle</span>
                Félicitations ! Votre rapport a été validé.
                <a href="<?= e($rapport_suivi['lien_pv_final']); ?>" class="btn-link ml-md" target="_blank">Consulter le Procès-Verbal Final</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-workflow-progress admin-card mt-xl">
        <h2 class="section-title">Progression du Workflow</h2>
        <div class="workflow-timeline">
            <?php foreach ($rapport_suivi['workflow_historique'] as $index => $etape): ?>
                <div class="timeline-item <?= strtolower($etape['statut']); ?>">
                    <div class="timeline-icon">
                        <span class="material-icons">
                            <?php
                            if ($etape['statut'] === 'Terminé' || $etape['statut'] === 'Approuvé') echo 'check_circle';
                            elseif ($etape['statut'] === 'En cours') echo 'more_horiz';
                            elseif ($etape['statut'] === 'En attente') echo 'pending';
                            elseif ($etape['statut'] === 'Non Conforme' || $etape['statut'] === 'Refusé') echo 'cancel';
                            else echo 'radio_button_unchecked';
                            ?>
                        </span>
                    </div>
                    <div class="timeline-content">
                        <h3><?= e($etape['etape']); ?></h3>
                        <span class="timeline-date"><?= e(date('d/m/Y H:i', strtotime($etape['date']))); ?></span>
                        <p><?= e($etape['details']); ?></p>
                        <?php if (!empty($etape['commentaires'])): ?>
                            <div class="comments-section">
                                <strong>Commentaires :</strong>
                                <ul>
                                    <?php foreach ($etape['commentaires'] as $comment): ?>
                                        <li>[<?= e($comment['agent']); ?>] <?= e($comment['texte']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($rapport_suivi['workflow_historique'])): ?>
            <p class="text-center text-muted">Historique du workflow non disponible pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // La fonction timeAgo est définie dans main.js et utilisée par le header,
        // mais si cette vue est chargée indépendamment, elle pourrait nécessiter sa propre définition
        // ou s'assurer que main.js est chargé avant.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour suivi_rapport.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px; /* Taille adaptée pour un suivi */
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

    .status-soumis, .status-en-contrôle-conformité, .status-en-évaluation-par-la-commission {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .status-non-conforme, .status-refusé {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .status-approuvé, .status-terminé {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-approuvé-sous-réserve, .status-en-attente-corrections {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }


    /* Alertes (réutilisées) */
    .alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-top: var(--spacing-lg); /* Au lieu de margin-bottom */
        margin-bottom: 0; /* Réinitialiser pour éviter double marge */
        text-align: left;
        border: 1px solid;
        background-color: var(--bg-primary); /* Fond blanc pour les alertes dans le contenu */
    }

    .alert-warning {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
        border-color: var(--accent-yellow-dark);
    }

    .alert-success {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
        border-color: var(--primary-green-dark);
    }

    .alert .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .btn-link {
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

    /* Progression du workflow */
    .workflow-timeline {
        position: relative;
        padding-left: var(--spacing-xl); /* Espace pour la ligne verticale */
        border-left: 2px solid var(--border-medium);
    }

    .timeline-item {
        margin-bottom: var(--spacing-xl);
        position: relative;
        padding-bottom: var(--spacing-lg); /* Espace entre les étapes */
    }

    .timeline-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -29px; /* Positionne le cercle sur la ligne */
        top: 0;
        width: 20px;
        height: 20px;
        background-color: var(--border-medium);
        border-radius: var(--border-radius-full);
        border: 3px solid var(--bg-primary);
        z-index: 1;
    }

    .timeline-item.terminé::before, .timeline-item.approuvé::before {
        background-color: var(--primary-green);
    }
    .timeline-item.en-cours::before, .timeline-item.en-attente::before {
        background-color: var(--primary-blue);
    }
    .timeline-item.non-conforme::before, .timeline-item.refusé::before {
        background-color: var(--accent-red);
    }

    .timeline-icon {
        position: absolute;
        left: -32px; /* Ajuste la position de l'icône */
        top: -4px;
        color: var(--text-white);
        font-size: var(--font-size-xl);
        z-index: 2;
    }

    .timeline-item.terminé .timeline-icon, .timeline-item.approuvé .timeline-icon { color: var(--text-white); }
    .timeline-item.en-cours .timeline-icon, .timeline-item.en-attente .timeline-icon { color: var(--text-white); }
    .timeline-item.non-conforme .timeline-icon, .timeline-item.refusé .timeline-icon { color: var(--text-white); }


    .timeline-content {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
        margin-left: var(--spacing-md); /* Décale le contenu de la ligne */
    }

    .timeline-content h3 {
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
    }

    .timeline-content .timeline-date {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        display: block;
        margin-bottom: var(--spacing-sm);
    }

    .timeline-content p {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        line-height: var(--line-height-normal);
    }

    .comments-section {
        margin-top: var(--spacing-md);
        padding-top: var(--spacing-md);
        border-top: 1px dashed var(--border-light);
    }

    .comments-section strong {
        display: block;
        font-size: var(--font-size-sm);
        color: var(--primary-blue-dark);
        margin-bottom: var(--spacing-xs);
    }

    .comments-section ul {
        list-style: none;
        padding: 0;
    }

    .comments-section li {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>