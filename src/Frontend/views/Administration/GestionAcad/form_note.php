<?php
// src/Frontend/views/Administration/GestionAcad/form_note.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour la note, les étudiants, les ECUEs (proviennent du contrôleur GestionAcadController)
// Ces données sont des exemples pour structurer la vue.
//

$note_a_modifier = $data['note_a_modifier'] ?? null;

$etudiants_disponibles = $data['etudiants_disponibles'] ?? [
    ['id' => 1, 'nom_complet' => 'Dupont Jean (ETU-2024-001)'],
    ['id' => 2, 'nom_complet' => 'Curie Marie (ETU-2024-002)'],
    ['id' => 3, 'nom_complet' => 'Voltaire François (ETU-2024-003)'],
];

$ecues_disponibles = $data['ecues_disponibles'] ?? [
    ['id' => 101, 'libelle' => 'INFO101 - Introduction à la Programmation'],
    ['id' => 102, 'libelle' => 'MATH101 - Algèbre Linéaire'],
    ['id' => 201, 'libelle' => 'PROJ201 - Gestion de Projet Agile'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Notes</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $note_a_modifier ? 'Modifier' : 'Saisir'; ?> une Note</h2>
        <form id="formNote" action="/admin/gestion-acad/notes/<?= $note_a_modifier ? 'update/' . e($note_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="etudiant_id">Étudiant :</label>
                <select id="etudiant_id" name="etudiant_id" required <?= $note_a_modifier ? 'disabled' : ''; ?>>
                    <option value="">Sélectionner un étudiant</option>
                    <?php foreach ($etudiants_disponibles as $etudiant): ?>
                        <option value="<?= e($etudiant['id']); ?>"
                            <?= ($note_a_modifier['etudiant_id'] ?? '') == $etudiant['id'] ? 'selected' : ''; ?>>
                            <?= e($etudiant['nom_complet']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($note_a_modifier): ?>
                    <input type="hidden" name="etudiant_id" value="<?= e($note_a_modifier['etudiant_id']); ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="ecue_id">ECUE (Élément Constitutif d'Unité d'Enseignement) :</label>
                <select id="ecue_id" name="ecue_id" required <?= $note_a_modifier ? 'disabled' : ''; ?>>
                    <option value="">Sélectionner un ECUE</option>
                    <?php foreach ($ecues_disponibles as $ecue): ?>
                        <option value="<?= e($ecue['id']); ?>"
                            <?= ($note_a_modifier['ecue_id'] ?? '') == $ecue['id'] ? 'selected' : ''; ?>>
                            <?= e($ecue['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($note_a_modifier): ?>
                    <input type="hidden" name="ecue_id" value="<?= e($note_a_modifier['ecue_id']); ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="note_valeur">Note (sur 20) :</label>
                <input type="number" id="note_valeur" name="note_valeur" min="0" max="20" step="0.01"
                       value="<?= e($note_a_modifier['note_valeur'] ?? ''); ?>" required placeholder="Ex: 15.50">
            </div>

            <div class="form-group">
                <label for="date_saisie">Date de Saisie :</label>
                <input type="date" id="date_saisie" name="date_saisie" value="<?= e($note_a_modifier['date_saisie'] ?? date('Y-m-d')); ?>" required>
            </div>

            <div class="form-group">
                <label for="commentaire">Commentaire (facultatif) :</label>
                <textarea id="commentaire" name="commentaire" rows="3" placeholder="Commentaire sur la note ou la performance..."><?= e($note_a_modifier['commentaire'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $note_a_modifier ? 'save' : 'add'; ?></span>
                <?= $note_a_modifier ? 'Enregistrer les modifications' : 'Saisir la Note'; ?>
            </button>
            <?php if ($note_a_modifier): ?>
                <a href="/admin/gestion-acad/notes" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formNote');
        if (form) {
            form.addEventListener('submit', function(event) {
                const noteValeur = parseFloat(document.getElementById('note_valeur').value);

                if (isNaN(noteValeur) || noteValeur < 0 || noteValeur > 20) {
                    alert('La note doit être un nombre entre 0 et 20.');
                    event.preventDefault();
                    return;
                }

                console.log("Formulaire de note soumis.");
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
    /* Styles spécifiques pour form_note.php */
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
    .form-group input[type="number"],
    .form-group input[type="date"],
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

    .form-group input:disabled,
    .form-group select:disabled {
        background-color: var(--primary-gray-light);
        color: var(--text-light);
        cursor: not-allowed;
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