<?php
// src/Frontend/views/Administration/Referentiels/crud_referentiel_generique.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données (éléments du référentiel, type de référentiel, élément à modifier)
// proviennent du contrôleur ReferentialController.
//

$type_referentiel = $data['type_referentiel'] ?? 'general'; // Ex: 'niveau_etude', 'specialite', 'grade', 'fonction', 'type_document'
$titre_referentiel = $data['titre_referentiel'] ?? 'Référentiel Général';
$elements_referentiel = $data['elements_referentiel'] ?? [];
$element_a_modifier = $data['element_a_modifier'] ?? null;

// Données fictives pour la démonstration si $elements_referentiel est vide
if (empty($elements_referentiel)) {
    switch ($type_referentiel) {
        case 'niveau_etude':
            $elements_referentiel = [
                ['id' => 1, 'code' => 'L1', 'libelle' => 'Licence 1'],
                ['id' => 2, 'code' => 'L2', 'libelle' => 'Licence 2'],
                ['id' => 3, 'code' => 'L3', 'libelle' => 'Licence 3'],
                ['id' => 4, 'code' => 'M1', 'libelle' => 'Master 1'],
                ['id' => 5, 'code' => 'M2', 'libelle' => 'Master 2'],
            ];
            $titre_referentiel = 'Niveaux d\'Étude';
            break;
        case 'specialite':
            $elements_referentiel = [
                ['id' => 1, 'code' => 'MIAGE', 'libelle' => 'Méthodes Informatiques Appliquées à la Gestion'],
                ['id' => 2, 'code' => 'INFO_SC', 'libelle' => 'Informatique Scientifique'],
                ['id' => 3, 'code' => 'CYBERSEC', 'libelle' => 'Cybersécurité'],
            ];
            $titre_referentiel = 'Spécialités de Formation';
            break;
        case 'grade':
            $elements_referentiel = [
                ['id' => 1, 'code' => 'ASSIST', 'libelle' => 'Assistant'],
                ['id' => 2, 'code' => 'MCF', 'libelle' => 'Maître de Conférences'],
                ['id' => 3, 'code' => 'PR', 'libelle' => 'Professeur'],
            ];
            $titre_referentiel = 'Grades Enseignants';
            break;
        case 'type_document_ref':
            $elements_referentiel = [
                ['id' => 1, 'code' => 'RAP', 'libelle' => 'Rapport de Soutenance'],
                ['id' => 2, 'code' => 'PV', 'libelle' => 'Procès-Verbal'],
                ['id' => 3, 'code' => 'ATT_SCO', 'libelle' => 'Attestation de Scolarité'],
            ];
            $titre_referentiel = 'Types de Documents de Référence';
            break;
        // Ajoutez d'autres cas pour les autres référentiels si nécessaire
        default:
            $elements_referentiel = [
                ['id' => 1, 'code' => 'REF001', 'libelle' => 'Élément Référentiel 1'],
                ['id' => 2, 'code' => 'REF002', 'libelle' => 'Élément Référentiel 2'],
            ];
            break;
    }
}

// Déterminer les champs à afficher dans le formulaire et la table
$champs_formulaire = ['libelle', 'code']; // Champs par défaut, peuvent être ajustés par type_referentiel
$champs_table = ['code', 'libelle']; // Champs par défaut pour la table

if ($type_referentiel === 'grade' || $type_referentiel === 'fonction') {
    // Les grades et fonctions ont un libellé et un code
}
// etc. pour d'autres types de référentiels si leurs champs varient.
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion du Référentiel : <?= e($titre_referentiel); ?></h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $element_a_modifier ? 'Modifier' : 'Ajouter'; ?> un Élément au Référentiel</h2>
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
                <textarea id="description" name="description" rows="3"><?= e($element_a_modifier['description'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $element_a_modifier ? 'save' : 'add'; ?></span>
                <?= $element_a_modifier ? 'Enregistrer les modifications' : 'Ajouter l\'Élément'; ?>
            </button>
            <?php if ($element_a_modifier): ?>
                <a href="/admin/referentiels/<?= e($type_referentiel); ?>" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Liste des Éléments du Référentiel</h2>
        <?php if (!empty($elements_referentiel)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($elements_referentiel as $element): ?>
                    <tr>
                        <td><?= e($element['libelle']); ?></td>
                        <td><?= e($element['code'] ?? 'N/A'); ?></td>
                        <td><?= e(mb_strimwidth($element['description'] ?? '', 0, 50, '...')); ?></td>
                        <td class="actions">
                            <a href="/admin/referentiels/<?= e($type_referentiel); ?>/edit/<?= e($element['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/referentiels/<?= e($type_referentiel); ?>/delete/<?= e($element['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer">
                                    <span class="material-icons">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Aucun élément enregistré pour ce référentiel pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/referentiels/<?= e($type_referentiel); ?>/create" class="btn btn-primary-blue">Ajouter le premier élément</a>
            </div>
        <?php endif; ?>
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
                console.log("Formulaire Référentiel soumis.");
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
    /* Styles spécifiques pour crud_referentiel_generique.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 900px;
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

    .form-help {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        margin-top: var(--spacing-xs);
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
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }


    /* Tableaux de données - réutilisation */
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
        text-decoration: none;
    }

    .btn-action:hover {
        background-color: var(--primary-gray-light);
    }

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
</style>