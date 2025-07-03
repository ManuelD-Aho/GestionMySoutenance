<?php
// src/Frontend/views/Administration/Referentiels/liste_referentiels.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Liste des types de référentiels disponibles.
// En production, cette liste pourrait être dynamique (par exemple, depuis une configuration de base de données
// ou une énumération de services via ServiceConfigurationSystemeInterface).
//

$types_referentiels = $data['types_referentiels'] ?? [
    ['code' => 'niveau_etude', 'libelle' => 'Niveaux d\'Étude', 'icon' => 'school'],
    ['code' => 'specialite', 'libelle' => 'Spécialités de Formation', 'icon' => 'category'],
    ['code' => 'grade', 'libelle' => 'Grades des Enseignants', 'icon' => 'workspace_premium'],
    ['code' => 'fonction', 'libelle' => 'Fonctions du Personnel', 'icon' => 'work'],
    ['code' => 'type_document_ref', 'libelle' => 'Types de Documents de Référence', 'icon' => 'description'],
    ['code' => 'statut_paiement', 'libelle' => 'Statuts de Paiement', 'icon' => 'payment'],
    ['code' => 'statut_reclamation', 'libelle' => 'Statuts de Réclamation', 'icon' => 'forum'],
    ['code' => 'critere_conformite_ref', 'libelle' => 'Critères de Conformité', 'icon' => 'rule'],
    ['code' => 'type_stage', 'libelle' => 'Types de Stage', 'icon' => 'badge'],
    ['code' => 'type_contrat', 'libelle' => 'Types de Contrat', 'icon' => 'handshake'], // Exemple, si pertinent
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Référentiels</h1>

    <section class="section-referentiels-list admin-card">
        <h2 class="section-title">Sélectionner un Référentiel à Gérer</h2>
        <p class="section-description">Cliquez sur un type de référentiel pour gérer ses éléments (ajouter, modifier, supprimer).</p>

        <?php if (!empty($types_referentiels)): ?>
            <div class="referentiels-grid">
                <?php foreach ($types_referentiels as $referentiel_type): ?>
                    <a href="/admin/referentiels/<?= e($referentiel_type['code']); ?>/list" class="referentiel-card">
                        <span class="material-icons referentiel-icon"><?= e($referentiel_type['icon']); ?></span>
                        <h3 class="referentiel-label"><?= e($referentiel_type['libelle']); ?></h3>
                        <p class="referentiel-code"><code><?= e($referentiel_type['code']); ?></code></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucun type de référentiel configuré pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript spécifique si nécessaire, mais cette page est principalement de navigation.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour liste_referentiels.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1200px;
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

    .section-description {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
    }

    /* Grille des cartes de référentiels */
    .referentiels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
        padding: var(--spacing-md);
    }

    .referentiel-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-xl);
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        text-decoration: none;
        color: var(--text-primary);
        transition: all var(--transition-fast);
        text-align: center;
    }

    .referentiel-card:hover {
        background-color: var(--primary-blue-light);
        color: var(--text-white);
        box-shadow: var(--shadow-md);
        transform: translateY(-5px);
    }

    .referentiel-card:hover .referentiel-icon {
        color: var(--text-white);
    }

    .referentiel-card:hover .referentiel-code {
        color: var(--text-white);
    }

    .referentiel-icon {
        font-size: var(--font-size-4xl);
        color: var(--primary-blue);
        margin-bottom: var(--spacing-md);
        transition: color var(--transition-fast);
    }

    .referentiel-label {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-xs);
        color: inherit; /* Hérite de la couleur de la carte */
    }

    .referentiel-code {
        font-size: var(--font-size-sm);
        color: var(--text-light);
        font-family: monospace;
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>