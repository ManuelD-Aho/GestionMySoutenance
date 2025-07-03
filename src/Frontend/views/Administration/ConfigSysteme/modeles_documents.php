<?php
// src/Frontend/views/Administration/ConfigSysteme/modeles_documents.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les modèles (proviennent du contrôleur ConfigSystemeController)
// Ces données sont des exemples pour structurer la vue.
//
$modeles_documents = $data['modeles_documents'] ?? [
    ['id' => 1, 'nom' => 'Attestation de Scolarité', 'type' => 'PDF', 'contexte' => 'Étudiant', 'variables' => '{NOM}, {PRENOM}, {ANNEE_ACADEMIQUE}'],
    ['id' => 2, 'nom' => 'Procès-Verbal de Soutenance', 'type' => 'PDF', 'contexte' => 'Commission', 'variables' => '{ETUDIANT}, {RAPPORT_TITRE}, {DECISION}'],
    ['id' => 3, 'nom' => 'Notification : Rapport Soumis', 'type' => 'Email', 'contexte' => 'Étudiant', 'variables' => '{NOM_ETUDIANT}, {TITRE_RAPPORT}'],
    ['id' => 4, 'nom' => 'Notification : Changement Statut Personnel', 'type' => 'Email', 'contexte' => 'Personnel', 'variables' => '{NOM_PERSONNEL}, {ANCIEN_STATUT}, {NOUVEAU_STATUT}'],
];

// Si un modèle est en mode modification, $modele_a_modifier serait passé
$modele_a_modifier = $data['modele_a_modifier'] ?? null;
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Modèles de Documents et Notifications</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $modele_a_modifier ? 'Modifier' : 'Ajouter'; ?> un Modèle</h2>
        <form id="formModeleDocument" action="/admin/config/modeles-documents/<?= $modele_a_modifier ? 'update/' . e($modele_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="nom">Nom du Modèle :</label>
                <input type="text" id="nom" name="nom" value="<?= e($modele_a_modifier['nom'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="type">Type :</label>
                <select id="type" name="type" required>
                    <option value="">Sélectionner un type</option>
                    <option value="PDF" <?= ($modele_a_modifier['type'] ?? '') === 'PDF' ? 'selected' : ''; ?>>Document PDF</option>
                    <option value="Email" <?= ($modele_a_modifier['type'] ?? '') === 'Email' ? 'selected' : ''; ?>>Notification Email</option>
                    <option value="Interne" <?= ($modele_a_modifier['type'] ?? '') === 'Interne' ? 'selected' : ''; ?>>Notification Interne</option>
                </select>
            </div>
            <div class="form-group">
                <label for="contexte">Contexte d'utilisation :</label>
                <input type="text" id="contexte" name="contexte" value="<?= e($modele_a_modifier['contexte'] ?? ''); ?>" placeholder="Ex: Étudiant, Commission, Personnel" required>
            </div>
            <div class="form-group">
                <label for="variables">Variables disponibles (séparées par des virgules) :</label>
                <input type="text" id="variables" name="variables" value="<?= e($modele_a_modifier['variables'] ?? ''); ?>" placeholder="Ex: {NOM}, {PRENOM}, {DATE}">
            </div>
            <div class="form-group">
                <label for="contenu">Contenu du Modèle (HTML ou Texte Brut) :</label>
                <textarea id="contenu" name="contenu" rows="10" class="wysiwyg-editor"><?= e($modele_a_modifier['contenu'] ?? ''); ?></textarea>
                <small class="form-help">Utilisez les variables entre accolades (ex: `{NOM}`) pour le contenu dynamique.</small>
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $modele_a_modifier ? 'save' : 'add'; ?></span>
                <?= $modele_a_modifier ? 'Enregistrer les modifications' : 'Ajouter le Modèle'; ?>
            </button>
            <?php if ($modele_a_modifier): ?>
                <a href="/admin/config/modeles-documents" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Liste des Modèles Existants</h2>
        <table class="data-table">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>Contexte</th>
                <th>Variables</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($modeles_documents)): ?>
                <?php foreach ($modeles_documents as $modele): ?>
                    <tr>
                        <td><?= e($modele['nom']); ?></td>
                        <td><?= e($modele['type']); ?></td>
                        <td><?= e($modele['contexte']); ?></td>
                        <td><span class="variables-tag"><?= e($modele['variables']); ?></span></td>
                        <td class="actions">
                            <a href="/admin/config/modeles-documents/edit/<?= e($modele['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/config/modeles-documents/delete/<?= e($modele['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle ?');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer">
                                    <span class="material-icons">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Aucun modèle de document ou de notification enregistré pour le moment.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation d'un éditeur WYSIWYG si vous en utilisez un (ex: TinyMCE, CKEditor)
        // C'est un placeholder, vous devrez inclure la bibliothèque JS du WYSIWYG dans app.php
        // ou une autre partie de votre layout.
        // if (typeof ClassicEditor !== 'undefined') { // Exemple pour CKEditor 5
        //     ClassicEditor
        //         .create(document.querySelector('#contenu'))
        //         .catch(error => {
        //             console.error('Erreur lors de l\'initialisation de l\'éditeur WYSIWYG', error);
        //         });
        // } else {
        //     console.warn('WYSIWYG editor library (e.g., CKEditor) not found. Textarea will be plain.');
        // }

        // Logique pour la gestion des messages flash si votre système en utilise
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            // Supprimer le message de la session après l'avoir lu
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour modeles_documents.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés de annee_academique.php */
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
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .form-group textarea.wysiwyg-editor {
        /* Styles de base pour le textarea, le WYSIWYG prendra le relais */
        resize: vertical;
        min-height: 150px;
    }

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
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

    /* Tableaux de données - réutilisation et adaptation */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: var(--spacing-md);
        font-size: var(--font-size-base);
    }

    .data-table th,
    .data-table td {
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-light);
        text-align: left;
        color: var(--text-primary);
    }

    .data-table th {
        background-color: var(--bg-secondary);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
    }

    .data-table tbody tr:nth-child(even) {
        background-color: var(--primary-gray-light);
    }

    .data-table tbody tr:hover {
        background-color: var(--border-medium);
        transition: background-color var(--transition-fast);
    }

    .actions {
        text-align: center;
        white-space: nowrap;
    }

    .btn-action {
        background: none;
        border: none;
        cursor: pointer;
        padding: var(--spacing-xs);
        border-radius: var(--border-radius-sm);
        transition: background-color var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-size: var(--font-size-xl);
    }

    .btn-action:hover {
        background-color: var(--primary-gray-light);
    }

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .variables-tag {
        background-color: var(--primary-gray-light);
        color: var(--text-secondary);
        padding: 0.2em 0.6em;
        border-radius: var(--border-radius-sm);
        font-family: monospace;
        font-size: var(--font-size-sm);
    }
</style>