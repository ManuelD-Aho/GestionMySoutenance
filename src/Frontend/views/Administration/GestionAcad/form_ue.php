<?php
// src/Frontend/views/Administration/GestionAcad/form_ue.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour l'UE à modifier (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//
$ue_a_modifier = $data['ue_a_modifier'] ?? null;
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Unités d'Enseignement (UE)</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $ue_a_modifier ? 'Modifier' : 'Ajouter'; ?> une UE</h2>
        <form id="formUE" action="/admin/gestion-acad/ues/<?= $ue_a_modifier ? 'update/' . e($ue_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="code_ue">Code UE :</label>
                <input type="text" id="code_ue" name="code_ue" value="<?= e($ue_a_modifier['code_ue'] ?? ''); ?>" required placeholder="Ex: UEINFO01">
            </div>
            <div class="form-group">
                <label for="libelle_ue">Libellé UE :</label>
                <input type="text" id="libelle_ue" name="libelle_ue" value="<?= e($ue_a_modifier['libelle'] ?? ''); ?>" required placeholder="Ex: Informatique Fondamentale">
            </div>
            <div class="form-group">
                <label for="description_ue">Description :</label>
                <textarea id="description_ue" name="description_ue" rows="5" placeholder="Brève description de l'Unité d'Enseignement..."><?= e($ue_a_modifier['description'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $ue_a_modifier ? 'save' : 'add'; ?></span>
                <?= $ue_a_modifier ? 'Enregistrer les modifications' : 'Ajouter l\'UE'; ?>
            </button>
            <?php if ($ue_a_modifier): ?>
                <a href="/admin/gestion-acad/ues" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formUE');
        if (form) {
            form.addEventListener('submit', function(event) {
                const codeUe = document.getElementById('code_ue').value.trim();
                const libelleUe = document.getElementById('libelle_ue').value.trim();

                if (codeUe === '' || libelleUe === '') {
                    alert('Veuillez remplir tous les champs obligatoires (Code, Libellé).');
                    event.preventDefault();
                    return;
                }
                console.log("Formulaire UE soumis.");
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
    /* Styles spécifiques pour form_ue.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 800px; /* Taille adaptée pour un formulaire simple */
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