<?php
// src/Frontend/views/Etudiant/Reclamation/suivi_reclamations.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les réclamations de l'étudiant (proviennent du ReclamationEtudiantController)
//
//

$reclamations_etudiant = $data['reclamations_etudiant'] ?? [
    ['id' => 1, 'sujet' => 'Problème de connexion espace personnel', 'categorie' => 'Accès plateforme', 'date_soumission' => '2025-06-25 10:00:00', 'statut' => 'En cours', 'historique_messages' => [
        ['type' => 'student', 'auteur' => 'Moi', 'date' => '2025-06-25 10:00:00', 'contenu' => 'Je ne peux pas me connecter à mon espace depuis hier soir.'],
        ['type' => 'staff', 'auteur' => 'RS-001', 'date' => '2025-06-25 14:30:00', 'contenu' => 'Nous avons identifié un problème avec votre compte. Il devrait être résolu d\'ici 24h. Nous vous informerons.'],
    ]],
    ['id' => 2, 'sujet' => 'Question sur les pénalités de retard', 'categorie' => 'Scolarité', 'date_soumission' => '2025-06-20 14:00:00', 'statut' => 'Résolue', 'historique_messages' => [
        ['type' => 'student', 'auteur' => 'Moi', 'date' => '2025-06-20 14:00:00', 'contenu' => 'Je n\'ai pas compris le montant de la pénalité affichée.'],
        ['type' => 'staff', 'auteur' => 'RS-001', 'date' => '2025-06-21 09:00:00', 'contenu' => 'La pénalité est due au dépassement de deux ans sans soumission. Voir politique de l\'établissement. Vous pouvez régulariser au bureau 101.'],
        ['type' => 'student', 'auteur' => 'Moi', 'date' => '2025-06-22 11:00:00', 'contenu' => 'Ok, merci pour la clarification. Je ferai le nécessaire.'],
    ]],
    ['id' => 3, 'sujet' => 'Demande de relevé de notes provisoire', 'categorie' => 'Documents', 'date_soumission' => '2025-06-18 11:00:00', 'statut' => 'Fermée', 'historique_messages' => [
        ['type' => 'student', 'auteur' => 'Moi', 'date' => '2025-06-18 11:00:00', 'contenu' => 'J\'ai besoin d\'un relevé de notes provisoire pour une candidature.'],
        ['type' => 'staff', 'auteur' => 'RS-001', 'date' => '2025-06-18 11:15:00', 'contenu' => 'Vous pouvez le générer directement depuis votre espace personnel dans la section "Mes Documents".'],
    ]],
];

// Mappage des statuts pour les indicateurs visuels
$status_map = [
    'En cours' => 'status-pending',
    'Résolue' => 'status-success',
    'Fermée' => 'status-closed',
    'En attente' => 'status-info',
    'Annulée' => 'status-canceled',
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Suivi de mes Réclamations</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Mes Réclamations</h2>
            <a href="/etudiant/reclamations/soumettre" class="btn btn-primary-blue">
                <span class="material-icons">add_box</span> Nouvelle Réclamation
            </a>
        </div>

        <?php if (!empty($reclamations_etudiant)): ?>
            <div class="reclamations-list">
                <?php foreach ($reclamations_etudiant as $reclamation): ?>
                    <div class="reclamation-item" data-reclamation-id="<?= e($reclamation['id']); ?>">
                        <div class="reclamation-summary">
                            <span class="material-icons reclamation-icon <?= strtolower(str_replace(' ', '-', $reclamation['statut'])); ?>">
                                <?php
                                if ($reclamation['statut'] === 'En cours') echo 'hourglass_empty';
                                elseif ($reclamation['statut'] === 'Résolue') echo 'check_circle';
                                elseif ($reclamation['statut'] === 'Fermée') echo 'archive';
                                else echo 'help_outline';
                                ?>
                            </span>
                            <div class="reclamation-info">
                                <h3><?= e($reclamation['sujet']); ?></h3>
                                <p class="reclamation-meta">
                                    Catégorie : <strong><?= e($reclamation['categorie']); ?></strong> |
                                    Soumise le : <span><?= e(date('d/m/Y H:i', strtotime($reclamation['date_soumission']))); ?></span>
                                </p>
                            </div>
                            <span class="status-indicator <?= e($status_map[$reclamation['statut']] ?? 'status-unknown'); ?>"><?= e($reclamation['statut']); ?></span>
                            <button class="btn-action toggle-details-btn" title="Voir les détails">
                                <span class="material-icons">expand_more</span>
                            </button>
                        </div>
                        <div class="reclamation-details" style="display:none;">
                            <div class="detail-block">
                                <h4>Description initiale :</h4>
                                <p><?= e($reclamation['historique_messages'][0]['contenu'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="detail-block message-history">
                                <h4>Historique des messages :</h4>
                                <ul class="message-list">
                                    <?php foreach ($reclamation['historique_messages'] as $message): ?>
                                        <li class="<?= e($message['type']); ?>">
                                            <strong><?= e($message['auteur']); ?></strong> (<?= e(date('d/m/Y H:i', strtotime($message['date']))); ?>) :
                                            <p><?= e($message['contenu']); ?></p>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="detail-block">
                                <a href="/etudiant/reclamations/view/<?= e($reclamation['id']); ?>" class="link-secondary">Voir le détail complet et répondre</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune réclamation soumise pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/etudiant/reclamations/soumettre" class="btn btn-primary-blue">Soumettre votre première réclamation</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reclamationsList = document.querySelector('.reclamations-list');

        if (reclamationsList) {
            reclamationsList.addEventListener('click', function(event) {
                const toggleBtn = event.target.closest('.toggle-details-btn');
                if (toggleBtn) {
                    const reclamationItem = toggleBtn.closest('.reclamation-item');
                    const detailsDiv = reclamationItem.querySelector('.reclamation-details');
                    const icon = toggleBtn.querySelector('.material-icons');

                    if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
                        detailsDiv.style.display = 'block';
                        icon.textContent = 'expand_less';
                        reclamationItem.classList.add('expanded');
                    } else {
                        detailsDiv.style.display = 'none';
                        icon.textContent = 'expand_more';
                        reclamationItem.classList.remove('expanded');
                    }
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
    /* Styles spécifiques pour suivi_reclamations.php */
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

    .section-header { /* Réutilisé des listes d'administration */
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    .section-title { /* Réutilisé des formulaires admin */
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        margin: 0;
    }

    /* Boutons */
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

    /* Liste des réclamations */
    .reclamations-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md); /* Espacement entre les cartes de réclamation */
    }

    .reclamation-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
        transition: all var(--transition-fast);
        overflow: hidden; /* Pour cacher les détails quand ils sont masqués */
    }

    .reclamation-item:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary-blue-light);
    }

    .reclamation-summary {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        cursor: pointer; /* Indique que c'est cliquable pour plus de détails */
    }

    .reclamation-icon {
        font-size: var(--font-size-3xl); /* Grande icône */
        color: var(--text-secondary); /* Couleur par défaut */
        flex-shrink: 0;
    }
    /* Couleurs spécifiques pour les icônes de réclamation */
    .reclamation-icon.en-cours { color: var(--accent-yellow-dark); }
    .reclamation-icon.résolue { color: var(--primary-green-dark); }
    .reclamation-icon.fermée { color: var(--text-secondary); }
    .reclamation-icon.en-attente { color: var(--primary-blue-dark); }
    /* Ajoutez d'autres statuts si besoin */


    .reclamation-info {
        flex-grow: 1;
    }

    .reclamation-info h3 {
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        margin: 0;
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
    }

    .reclamation-meta {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    .reclamation-meta strong {
        color: var(--text-primary);
    }


    /* Indicateurs de statut */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 80px;
        text-align: center;
        flex-shrink: 0; /* Empêche le badge de se compresser */
    }

    .status-pending { background-color: var(--accent-yellow-light); color: var(--accent-yellow-dark); }
    .status-success { background-color: var(--primary-green-light); color: var(--primary-green-dark); }
    .status-closed { background-color: var(--border-medium); color: var(--text-secondary); }
    .status-info { background-color: var(--primary-blue-light); color: var(--primary-blue-dark); }
    .status-canceled { background-color: var(--accent-red-light); color: var(--accent-red-dark); }
    .status-unknown { background-color: var(--primary-gray-light); color: var(--text-secondary); }


    .btn-action.toggle-details-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: var(--spacing-xs);
        border-radius: var(--border-radius-sm);
        transition: background-color var(--transition-fast), transform var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }
    .btn-action.toggle-details-btn:hover {
        background-color: var(--primary-gray-light);
    }
    .reclamation-item.expanded .toggle-details-btn .material-icons {
        transform: rotate(180deg);
    }

    /* Détails de la réclamation */
    .reclamation-details {
        padding-top: var(--spacing-md);
        margin-top: var(--spacing-md);
        border-top: 1px dashed var(--border-light);
        display: none; /* Géré par JS */
    }

    .detail-block {
        margin-bottom: var(--spacing-lg);
    }

    .detail-block h4 {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        font-weight: var(--font-weight-semibold);
        border-bottom: 1px dotted var(--border-light);
        padding-bottom: var(--spacing-xs);
    }

    .detail-block p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: var(--line-height-normal);
    }

    .message-history .message-list {
        list-style: none;
        padding: 0;
    }

    .message-history .message-list li {
        background-color: var(--primary-gray-light);
        border-radius: var(--border-radius-sm);
        padding: var(--spacing-sm);
        margin-bottom: var(--spacing-sm);
        font-size: var(--font-size-sm);
        color: var(--text-primary);
    }

    .message-history .message-list li.student {
        background-color: var(--primary-blue-light);
        color: var(--text-white);
        text-align: right; /* Les messages de l'étudiant à droite */
    }
    .message-history .message-list li.student strong { color: var(--text-white); }
    .message-history .message-list li.student p { color: var(--text-white); }


    .message-history .message-list li.staff {
        background-color: var(--primary-white); /* Les messages du staff à gauche */
        border: 1px solid var(--border-light);
    }

    .message-history .message-list li strong {
        display: block;
        font-size: var(--font-size-xs);
        margin-bottom: var(--spacing-xs);
        color: var(--primary-blue-dark);
    }

    .message-history .message-list li p {
        font-size: var(--font-size-base);
        margin: 0;
    }

    .link-secondary { /* Réutilisé */
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: var(--font-weight-medium);
        transition: color var(--transition-fast);
    }
    .link-secondary:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }


    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>