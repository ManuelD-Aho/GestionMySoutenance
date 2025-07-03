<?php
// src/Frontend/views/Administration/Referentiels/form_referentiel_generique.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données (type de référentiel, élément à modifier) proviennent du contrôleur ReferentialController.
//

$type_referentiel = $data['type_referentiel'] ?? 'default_referential_type'; // Exemple: 'niveau_etude', 'specialite', 'grade'
$titre_formulaire = $data['titre_formulaire'] ?? 'Élément de Référentiel'; // Titre spécifique si passé par le contrôleur
$element_a_modifier = $data['element_a_modifier'] ?? null;

// Déterminer le titre H1 en fonction du type de référentiel ou du titre passé
$page_h1_title = 'Gestion du Référentiel : ';
switch ($type_referentiel) {
    case 'niveau_etude':
        $page_h1_title .= 'Niveaux d\'Étude';
        break;
    case 'specialite':
        $page_h1_title .= 'Spécialités de Formation';
        break;
    case 'grade':
        $page_h1_title .= 'Grades Enseignants';
        break;
    case 'fonction':
        $page_h1_title .= 'Fonctions du Personnel';
        break;
    case 'type_document_ref':
        $page_h1_title .= 'Types de Documents de Référence';
        break;
    case 'statut_paiement':
        $page_h1_title .= 'Statuts de Paiement';
        break;
    case 'statut_reclamation':
        $page_h1_title .= 'Statuts de Réclamation';
        break;
    default:
        $page_h1_title .= e($type_referentiel);
        break;
}

?>

<div class="admin-module-container">
    <h1 class="admin-title"><?= e($page_h1_title); ?></h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $element_a_modifier ? 'Modifier' : 'Ajouter'; ?> un <?= e($titre_formulaire); ?></h2>
        <form id="formReferentiel" action="/admin/referentiels/<?= e($type_referentiel); ?>/<?= $element_a_modifier ? 'update/' . e($element_a_modifier['id']) : 'create'; ?>" method="POST">
            <input type="hidden" name="type_referentiel" value="<?= e($type_referentiel); ?>">

            <div class="form-group">
                <label for="libelle">Libellé :</label>
                <input type="text" id="libelle" name="libelle" value="<?= e($element_a_modifier['libelle'] ?? ''); ?>" required placeholder="Libellé de l'élément">
            </div>
            <div class="form-group">
                <label for="code">Code (optionnel, si applicable) :</label>
                <input type="text" id="code" name="code" value="<?= e($element_a_modifier['code'] ?? ''); ?>" placeholder="Code unique (ex: L1, M2)">
                <small class="form-help">Utilisé pour les références techniques (ex: code de niveau, de spécialité).</small>
            </div>
            <div class="form-group">
                <label for="description">Description (facultatif) :</label>
                <textarea id="description" name="description" rows="3" placeholder="Brève description de cet élément..."><?= e($element_a_modifier['description'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $element_a_modifier ? 'save' : 'add'; ?></span>
                <?= $element_a_modifier ? 'Enregistrer les modifications' : 'Ajouter l\'Élément'; ?>
            </button>
            <a href="/admin/referentiels/<?= e($type_referentiel); ?>/list" class="btn btn-secondary-gray ml-md">Annuler</a>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formReferentiel');
        if (form) {
            form.addEventListener('submit', function(event) {
                const libelle = document.getElementById('libelle').value.trim();
                if (libelle === '') {
                    alert('Le libellé est obligatoire.');
                    event.preventDefault();
                    return;
                }
                console.log("Formulaire Référentiel générique soumis.");
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
    /* Styles spécifiques pour form_referentiel_generique.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 800px; /* Adapté à un formulaire */
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

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
    }

    /* Boutons - réutilisation */
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