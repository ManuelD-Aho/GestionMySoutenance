<?php
// src/Frontend/views/Administration/Habilitations/form_traitement.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le traitement à modifier (proviennent du contrôleur HabilitationController)
// Ces données sont des exemples pour structurer la vue.
//

$traitement_a_modifier = $data['traitement_a_modifier'] ?? null;
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Traitements (Permissions Atomiques)</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $traitement_a_modifier ? 'Modifier' : 'Ajouter'; ?> un Traitement</h2>
        <form id="formTraitement" action="/admin/habilitations/traitements/<?= $traitement_a_modifier ? 'update/' . e($traitement_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="code_traitement">Code du Traitement (Ex: TRAIT_RAPPORT_SOUMETTRE) :</label>
                <input type="text" id="code_traitement" name="code_traitement" value="<?= e($traitement_a_modifier['code'] ?? ''); ?>" required placeholder="Ex: TRAIT_COMMISSION_VOTER">
            </div>
            <div class="form-group">
                <label for="libelle_traitement">Libellé du Traitement :</label>
                <input type="text" id="libelle_traitement" name="libelle" value="<?= e($traitement_a_modifier['libelle'] ?? ''); ?>" required placeholder="Ex: Permettre de voter sur un rapport">
            </div>
            <div class="form-group">
                <label for="description_traitement">Description :</label>
                <textarea id="description_traitement" name="description" rows="5" placeholder="Brève description de l'action ou de la fonctionnalité..."><?= e($traitement_a_modifier['description'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $traitement_a_modifier ? 'save' : 'add'; ?></span>
                <?= $traitement_a_modifier ? 'Enregistrer les modifications' : 'Ajouter le Traitement'; ?>
            </button>
            <?php if ($traitement_a_modifier): ?>
                <a href="/admin/habilitations/traitements" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formTraitement');
        if (form) {
            form.addEventListener('submit', function(event) {
                const codeTraitement = document.getElementById('code_traitement').value.trim();
                const libelleTraitement = document.getElementById('libelle_traitement').value.trim();

                if (codeTraitement === '' || libelleTraitement === '') {
                    alert('Veuillez remplir tous les champs obligatoires (Code et Libellé).');
                    event.preventDefault();
                    return;
                }
                console.log("Formulaire Traitement soumis.");
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
    /* Styles spécifiques pour form_traitement.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 800px;
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
    .form-group textarea:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    /* Boutons - réutilisation des styles existants */
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
</style>