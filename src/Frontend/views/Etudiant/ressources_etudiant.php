<?php
// src/Frontend/views/Etudiant/ressources_etudiant.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les ressources (proviennent du RessourcesEtudiantController)
//
//

$resources = $data['resources'] ?? [
    'guides_methodologiques' => [
        ['id' => 1, 'titre' => 'Guide de Rédaction du Rapport de Soutenance', 'url' => '/assets/docs/guide_redaction.pdf', 'description' => 'Étapes détaillées pour la rédaction de votre rapport.'],
        ['id' => 2, 'titre' => 'Conseils pour une Présentation Orale Réussie', 'url' => '/assets/docs/conseils_presentation.pdf', 'description' => 'Optimisez votre soutenance orale.'],
    ],
    'exemples_structure_rapport' => [
        ['id' => 1, 'titre' => 'Exemple: Rapport de Stage Master 2', 'url' => '/assets/docs/exemple_rapport_m2.pdf', 'description' => 'Structure d\'un bon rapport de stage.'],
        ['id' => 2, 'titre' => 'Exemple: Rapport de Projet de Fin d\'Études', 'url' => '/assets/docs/exemple_pfe.pdf', 'description' => 'Organisation typique d\'un PFE.'],
    ],
    'criteres_evaluation' => [
        ['id' => 1, 'titre' => 'Grille d\'Évaluation de la Commission', 'url' => '/assets/docs/grille_evaluation.pdf', 'description' => 'Critères utilisés par le jury.'],
        ['id' => 2, 'titre' => 'Critères de Conformité Administrative', 'url' => '/assets/docs/criteres_conformite.pdf', 'description' => 'Ce que le service de conformité vérifie.'],
    ],
    'faq' => [
        ['id' => 1, 'question' => 'Comment soumettre mon rapport ?', 'reponse' => 'Rendez-vous dans la section "Soumettre mon Rapport" de votre espace personnel et suivez les étapes.'],
        ['id' => 2, 'question' => 'Comment consulter mes notes ?', 'reponse' => 'Vos relevés provisoires sont dans "Mes Documents". Les officiels sont publiés par l\'administration.'],
        ['id' => 3, 'question' => 'Que faire si mon rapport est non conforme ?', 'reponse' => 'Vous recevrez des commentaires détaillés. Modifiez votre rapport et re-soumettez-le via l\'interface "Soumettre mes Corrections".'],
    ],
    'coordonnees_support' => [
        ['service' => 'Support Technique', 'email' => 'support.tech@univ.com', 'telephone' => '+225 00000000'],
        ['service' => 'Service Scolarité', 'email' => 'scolarite@univ.com', 'telephone' => '+225 11111111'],
        ['service' => 'Conseillers Pédagogiques', 'email' => 'conseil.pedago@univ.com', 'telephone' => '+225 22222222'],
    ],
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Ressources & Aide</h1>

    <section class="section-resources admin-card">
        <h2 class="section-title">Guides Méthodologiques</h2>
        <?php if (!empty($resources['guides_methodologiques'])): ?>
            <ul class="resource-list">
                <?php foreach ($resources['guides_methodologiques'] as $guide): ?>
                    <li class="resource-item">
                        <span class="material-icons resource-icon">auto_stories</span>
                        <div class="resource-info">
                            <h3><?= e($guide['titre']); ?></h3>
                            <p><?= e($guide['description']); ?></p>
                        </div>
                        <a href="<?= e($guide['url']); ?>" target="_blank" download class="btn-action download-btn" title="Télécharger">
                            <span class="material-icons">download</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucun guide méthodologique disponible pour le moment.</p>
        <?php endif; ?>
    </section>

    <section class="section-resources admin-card mt-xl">
        <h2 class="section-title">Exemples de Structure de Rapport</h2>
        <?php if (!empty($resources['exemples_structure_rapport'])): ?>
            <ul class="resource-list">
                <?php foreach ($resources['exemples_structure_rapport'] as $exemple): ?>
                    <li class="resource-item">
                        <span class="material-icons resource-icon">description</span>
                        <div class="resource-info">
                            <h3><?= e($exemple['titre']); ?></h3>
                            <p><?= e($exemple['description']); ?></p>
                        </div>
                        <a href="<?= e($exemple['url']); ?>" target="_blank" download class="btn-action download-btn" title="Télécharger">
                            <span class="material-icons">download</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucun exemple de structure de rapport disponible.</p>
        <?php endif; ?>
    </section>

    <section class="section-resources admin-card mt-xl">
        <h2 class="section-title">Critères d'Évaluation</h2>
        <?php if (!empty($resources['criteres_evaluation'])): ?>
            <ul class="resource-list">
                <?php foreach ($resources['criteres_evaluation'] as $critere): ?>
                    <li class="resource-item">
                        <span class="material-icons resource-icon">rule</span>
                        <div class="resource-info">
                            <h3><?= e($critere['titre']); ?></h3>
                            <p><?= e($critere['description']); ?></p>
                        </div>
                        <a href="<?= e($critere['url']); ?>" target="_blank" download class="btn-action download-btn" title="Télécharger">
                            <span class="material-icons">download</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucun critère d'évaluation disponible.</p>
        <?php endif; ?>
    </section>

    <section class="section-faq admin-card mt-xl">
        <h2 class="section-title">Foire Aux Questions (FAQ)</h2>
        <?php if (!empty($resources['faq'])): ?>
            <div class="faq-list">
                <?php foreach ($resources['faq'] as $qa): ?>
                    <div class="faq-item">
                        <button class="faq-question-toggle">
                            <h3><?= e($qa['question']); ?></h3>
                            <span class="material-icons expand-icon">expand_more</span>
                        </button>
                        <div class="faq-answer" style="display: none;">
                            <p><?= e($qa['reponse']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune FAQ disponible pour le moment.</p>
        <?php endif; ?>
    </section>

    <section class="section-support admin-card mt-xl">
        <h2 class="section-title">Coordonnées des Services de Support</h2>
        <?php if (!empty($resources['coordonnees_support'])): ?>
            <ul class="contact-list">
                <?php foreach ($resources['coordonnees_support'] as $contact): ?>
                    <li class="contact-item">
                        <h3><?= e($contact['service']); ?></h3>
                        <?php if ($contact['email']): ?>
                            <p><span class="material-icons">email</span> Email : <a href="mailto:<?= e($contact['email']); ?>"><?= e($contact['email']); ?></a></p>
                        <?php endif; ?>
                        <?php if ($contact['telephone']): ?>
                            <p><span class="material-icons">phone</span> Téléphone : <a href="tel:<?= e($contact['telephone']); ?>"><?= e($contact['telephone']); ?></a></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucune coordonnée de support disponible pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour les FAQ (accordeon)
        document.querySelectorAll('.faq-question-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const answer = this.nextElementSibling; // L'élément div.faq-answer
                const icon = this.querySelector('.expand-icon');

                if (answer.style.display === 'block' || answer.style.display === '') {
                    answer.style.display = 'none';
                    icon.textContent = 'expand_more';
                } else {
                    answer.style.display = 'block';
                    icon.textContent = 'expand_less';
                }
            });
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
    /* Styles spécifiques pour ressources_etudiant.php */
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

    .section-title { /* Réutilisé des formulaires admin */
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* Listes de ressources (guides, exemples, critères) */
    .resource-list {
        list-style: none;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .resource-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .resource-icon {
        font-size: var(--font-size-2xl);
        color: var(--primary-blue);
        flex-shrink: 0;
    }

    .resource-info {
        flex-grow: 1;
    }

    .resource-info h3 {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin: 0;
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
    }

    .resource-info p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    .btn-action.download-btn { /* Réutilisé de mes_documents.php */
        background: none;
        border: none;
        cursor: pointer;
        padding: var(--spacing-xs);
        border-radius: var(--border-radius-sm);
        transition: background-color var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-green);
        font-size: var(--font-size-xl);
        text-decoration: none;
    }
    .btn-action.download-btn:hover {
        background-color: rgba(16, 185, 129, 0.1);
    }

    /* FAQ */
    .faq-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .faq-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden; /* Pour l'animation de display */
    }

    .faq-question-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: var(--spacing-md);
        background-color: var(--bg-secondary);
        border: none;
        cursor: pointer;
        text-align: left;
        transition: background-color var(--transition-fast);
    }

    .faq-question-toggle:hover {
        background-color: var(--primary-gray-light);
    }

    .faq-question-toggle h3 {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin: 0;
        font-weight: var(--font-weight-semibold);
        flex-grow: 1;
    }

    .faq-question-toggle .expand-icon {
        font-size: var(--font-size-xl);
        color: var(--text-secondary);
        transition: transform var(--transition-fast);
    }

    .faq-item.expanded .expand-icon {
        transform: rotate(180deg);
    }

    .faq-answer {
        padding: var(--spacing-md);
        padding-top: 0; /* Pas de padding top pour coller au titre */
        border-top: 1px dashed var(--border-light);
        background-color: var(--primary-white);
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
        line-height: var(--line-height-normal);
    }
    .faq-answer p { margin-bottom: var(--spacing-sm); }
    .faq-answer p:last-child { margin-bottom: 0; }


    /* Coordonnées de support */
    .contact-list {
        list-style: none;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-md);
    }

    .contact-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
    }

    .contact-item h3 {
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        margin-bottom: var(--spacing-sm);
        font-weight: var(--font-weight-semibold);
    }

    .contact-item p {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        margin-bottom: var(--spacing-xs);
    }

    .contact-item p .material-icons {
        font-size: var(--font-size-xl);
        color: var(--text-secondary);
    }

    .contact-item a {
        color: var(--primary-blue);
        text-decoration: none;
        transition: color var(--transition-fast);
    }
    .contact-item a:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
</style>