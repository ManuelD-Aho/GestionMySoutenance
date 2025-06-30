<?php
// src/Frontend/views/Administration/GestionAcad/form_ecue.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour l'ECUE et les UEs (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//

$ecue_a_modifier = $data['ecue_a_modifier'] ?? null;
$ues_disponibles = $data['ues_disponibles'] ?? [
    ['id' => 1, 'libelle' => 'UE Fondamentaux Informatiques'],
    ['id' => 2, 'libelle' => 'UE Développement Logiciel Avancé'],
    ['id' => 3, 'libelle' => 'UE Réseaux et Sécurité'],
    ['id' => 4, 'libelle' => 'UE Gestion de Projet'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des ECUE</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $ecue_a_modifier ? 'Modifier' : 'Ajouter'; ?> un ECUE</h2>
        <form id="formEcue" action="/admin/gestion-acad/ecues/<?= $ecue_a_modifier ? 'update/' . e($ecue_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="code_ecue">Code ECUE :</label>
                <input type="text" id="code_ecue" name="code_ecue" value="<?= e($ecue_a_modifier['code_ecue'] ?? ''); ?>" required placeholder="Ex: INFO101">
            </div>
            <div class="form-group">
                <label for="libelle_ecue">Libellé ECUE :</label>
                <input type="text" id="libelle_ecue" name="libelle_ecue" value="<?= e($ecue_a_modifier['libelle'] ?? ''); ?>" required placeholder="Ex: Introduction à la Programmation">
            </div>
            <div class="form-group">
                <label for="description_ecue">Description :</label>
                <textarea id="description_ecue" name="description_ecue" rows="5" placeholder="Brève description de l'ECUE..."><?= e($ecue_a_modifier['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="ue_id">Unité d'Enseignement (UE) associée :</label>
                <select id="ue_id" name="ue_id" required>
                    <option value="">Sélectionner une UE</option>
                    <?php foreach ($ues_disponibles as $ue): ?>
                        <option value="<?= e($ue['id']); ?>"
                            <?= ($ecue_a_modifier['ue_id'] ?? '') == $ue['id'] ? 'selected' : ''; ?>>
                            <?= e($ue['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $ecue_a_modifier ? 'save' : 'add'; ?></span>
                <?= $ecue_a_modifier ? 'Enregistrer les modifications' : 'Ajouter l\'ECUE'; ?>
            </button>
            <?php if ($ecue_a_modifier): ?>
                <a href="/admin/gestion-acad/ecues" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript pour la gestion des formulaires
        const form = document.getElementById('formEcue');
        if (form) {
            form.addEventListener('submit', function(event) {
                // Exemple de validation front-end simple
                const codeEcue = document.getElementById('code_ecue').value.trim();
                const libelleEcue = document.getElementById('libelle_ecue').value.trim();
                const ueId = document.getElementById('ue_id').value;

                if (codeEcue === '' || libelleEcue === '' || ueId === '') {
                    alert('Veuillez remplir tous les champs obligatoires (Code, Libellé, UE).');
                    event.preventDefault();
                    return;
                }

                console.log("Formulaire ECUE soumis.");
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
    /* Styles spécifiques pour form_ecue.php */
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