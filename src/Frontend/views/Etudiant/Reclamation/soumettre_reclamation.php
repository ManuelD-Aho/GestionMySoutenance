<?php
// src/Frontend/views/Etudiant/Reclamation/soumettre_reclamation.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les catégories de réclamations (proviennent du ReclamationEtudiantController)
//
//

$categories_reclamation = $data['categories_reclamation'] ?? [
    ['id' => 1, 'libelle' => 'Problème d\'accès à la plateforme'],
    ['id' => 2, 'libelle' => 'Statut du rapport de soutenance'],
    ['id' => 3, 'libelle' => 'Clarification de procédure (soutenance, stage)'],
    ['id' => 4, 'libelle' => 'Problème d\'inscription/scolarité'],
    ['id' => 5, 'libelle' => 'Demande de reprise de processus (nouveau stage)'],
    ['id' => 6, 'libelle' => 'Autre'],
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Soumettre une Réclamation</h1>

    <section class="section-form admin-card">
        <h2 class="section-title">Détails de votre Réclamation</h2>
        <p class="section-description">Veuillez décrire votre problème ou votre requête de manière claire et détaillée.</p>

        <form id="formSoumettreReclamation" action="/etudiant/reclamations/soumettre" method="POST">
            <div class="form-group">
                <label for="categorie_id">Catégorie de la réclamation :</label>
                <select id="categorie_id" name="categorie_id" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories_reclamation as $categorie): ?>
                        <option value="<?= e($categorie['id']); ?>"><?= e($categorie['libelle']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="sujet">Sujet :</label>
                <input type="text" id="sujet" name="sujet" required placeholder="Ex: Problème de connexion après mise à jour">
                <small class="form-help">Un titre concis pour votre réclamation.</small>
            </div>
            <div class="form-group">
                <label for="description">Description détaillée :</label>
                <textarea id="description" name="description" rows="10" required placeholder="Décrivez votre problème ou votre requête en détail, en fournissant toutes les informations pertinentes..."></textarea>
                <small class="form-help">Plus votre description sera précise, plus notre équipe pourra vous aider rapidement.</small>
            </div>

            <div class="form-actions mt-xl">
                <button type="submit" class="btn btn-primary-blue">
                    <span class="material-icons">send</span> Soumettre la Réclamation
                </button>
                <a href="/etudiant/reclamations/suivi" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">cancel</span> Annuler
                </a>
            </div>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formSoumettreReclamation');
        if (form) {
            form.addEventListener('submit', function(event) {
                const categorieId = document.getElementById('categorie_id').value;
                const sujet = document.getElementById('sujet').value.trim();
                const description = document.getElementById('description').value.trim();

                if (!categorieId || !sujet || !description) {
                    alert('Veuillez remplir tous les champs obligatoires (Catégorie, Sujet, Description).');
                    event.preventDefault();
                    return;
                }
                console.log("Formulaire de soumission de réclamation soumis.");
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
    /* Styles spécifiques pour soumettre_reclamation.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 800px; /* Taille adaptée au formulaire */
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

    /* Formulaires - réutilisation */
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
        min-height: 150px; /* Plus grande taille pour la description */
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
    .mt-xl { margin-top: var(--spacing-xl); }
</style>