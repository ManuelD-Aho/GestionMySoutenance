<?php
// src/Frontend/views/Commission/PV/consulter_pv.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le PV (proviennent du contrôleur PvController)
// Ces données sont des exemples pour structurer la vue.
//
//

$pv_details = $data['pv_details'] ?? [
    'id' => 1,
    'numero_pv' => 'PV-2025-0010',
    'date_redaction' => '2025-06-28 10:00:00',
    'session_libelle' => 'Session de Validation Juin 2025 - Vague 1',
    'rapport_id' => 'RAP-2025-0045',
    'rapport_titre' => 'Optimisation des Processus Logistiques par IA',
    'etudiant_nom_complet' => 'Dupont Jean (ETU-2025-0001)',
    'president_commission_nom' => 'Pr. Martin Sophie',
    'rapporteur_nom' => 'Dr. Bernard Paul',
    'decision_finale' => 'Approuvé en l\'état',
    'note_attribuee' => 16.5,
    'contenu_textuel_pv' => '<p>Le rapport intitulé "Optimisation des Processus Logistiques par IA" présenté par l\'étudiant Jean Dupont a été évalué par la commission.</p><p><strong>Observations générales :</strong> Le travail démontre une excellente compréhension des concepts d\'IA appliquée à la logistique. La méthodologie est solide et les résultats obtenus sont pertinents.</p><p><strong>Points forts :</strong> Originalité de l\'approche, clarté de la présentation, pertinence des outils utilisés.</p><p><strong>Recommandations :</strong> Une section sur les limites de l\'étude et les perspectives futures pourrait être légèrement approfondie. La bibliographie est complète.</p><p><strong>Décision :</strong> Le rapport est <strong>approuvé en l\'état</strong> avec la note de 16.5/20. Félicitations à l\'étudiant.</p>',
    'statut_validation' => 'Validé', // Validé, En attente approbation, Rejeté
    'lien_pdf' => '/assets/docs/pv-2025-0010.pdf', // Lien réel vers le fichier généré
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Consultation du Procès-Verbal</h1>

    <section class="section-pv-details admin-card">
        <div class="section-header">
            <h2 class="section-title">Procès-Verbal : <?= e($pv_details['numero_pv']); ?></h2>
            <div class="pv-actions">
                <a href="<?= e($pv_details['lien_pdf']); ?>" target="_blank" class="btn btn-primary-blue">
                    <span class="material-icons">picture_as_pdf</span> Télécharger PDF
                </a>
                <?php if ($pv_details['statut_validation'] === 'Validé' && isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'ADMIN' || $_SESSION['user_role'] === 'COMMISSION')): // Exemple de condition basée sur le rôle ?>
                    <button class="btn btn-secondary-gray ml-md" onclick="window.print();">
                        <span class="material-icons">print</span> Imprimer
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="pv-metadata-grid">
            <div class="meta-item">
                <strong>Numéro PV :</strong> <span><?= e($pv_details['numero_pv']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Date Rédaction :</strong> <span><?= e(date('d/m/Y H:i', strtotime($pv_details['date_redaction']))); ?></span>
            </div>
            <div class="meta-item">
                <strong>Session :</strong> <span><?= e($pv_details['session_libelle']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Rapport Associé :</strong> <span><?= e($pv_details['rapport_titre']); ?> (ID: <?= e($pv_details['rapport_id']); ?>)</span>
            </div>
            <div class="meta-item">
                <strong>Étudiant :</strong> <span><?= e($pv_details['etudiant_nom_complet']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Président Commission :</strong> <span><?= e($pv_details['president_commission_nom']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Rapporteur :</strong> <span><?= e($pv_details['rapporteur_nom']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Décision Finale :</strong> <span class="decision-status decision-status-<?= e(strtolower(str_replace(' ', '-', $pv_details['decision_finale']))); ?>"><?= e($pv_details['decision_finale']); ?></span>
            </div>
            <div class="meta-item">
                <strong>Note Attribuée :</strong> <span class="note-value <?= $pv_details['note_attribuee'] < 10 ? 'note-fail' : ($pv_details['note_attribuee'] >= 15 ? 'note-excellent' : 'note-pass'); ?>"><?= e(number_format($pv_details['note_attribuee'], 2, ',', '')); ?> / 20</span>
            </div>
            <div class="meta-item">
                <strong>Statut du PV :</strong> <span class="status-indicator status-<?= e(strtolower($pv_details['statut_validation'])); ?>"><?= e($pv_details['statut_validation']); ?></span>
            </div>
        </div>

        <div class="pv-content-section mt-xl">
            <h3>Contenu du Procès-Verbal</h3>
            <div class="pv-content-display">
                <?= $pv_details['contenu_textuel_pv']; // Le contenu HTML est déjà échappé par le service de rédaction ?>
            </div>
        </div>

    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript si nécessaire.
        // Par exemple, une fonction d'impression plus avancée si le simple window.print() ne suffit pas.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour consulter_pv.php */
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

    /* Actions spécifiques au PV */
    .pv-actions {
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

    /* Métadonnées du PV */
    .pv-metadata-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xl);
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

    /* Statuts spécifiques (réutilisation de liste_notes ou autres) */
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

    .decision-status-approuvé-en-l-état {
        color: var(--primary-green-dark);
    }
    .decision-status-refusé {
        color: var(--accent-red-dark);
    }
    .decision-status-approuvé-sous-réserve-de-corrections-mineures {
        color: var(--accent-yellow-dark);
    }
    /* Style pour la note */
    .note-value {
        font-weight: var(--font-weight-bold);
        padding: 0.2em 0.5em;
        border-radius: var(--border-radius-sm);
    }

    .note-fail {
        color: var(--accent-red-dark);
        background-color: var(--accent-red-light);
    }

    .note-pass {
        color: var(--primary-blue-dark);
        background-color: var(--primary-blue-light);
    }

    .note-excellent {
        color: var(--primary-green-dark);
        background-color: var(--primary-green-light);
    }


    /* Contenu du PV */
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

    /* Styles pour le contenu HTML (si l'éditeur WYSIWYG est utilisé) */
    .pv-content-display p { margin-bottom: var(--spacing-sm); }
    .pv-content-display strong { font-weight: var(--font-weight-bold); }
    .pv-content-display em { font-style: italic; }
    .pv-content-display ul, .pv-content-display ol {
        margin-left: var(--spacing-lg);
        margin-bottom: var(--spacing-sm);
    }
    .pv-content-display li { margin-bottom: var(--spacing-xs); }


    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }