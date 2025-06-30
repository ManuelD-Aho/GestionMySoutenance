<?php
// src/Frontend/views/Administration/ConfigSysteme/annee_academique.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les années académiques (proviennent du contrôleur ConfigSystemeController ou AnneeAcademiqueController)
//
//

$annees_academiques = $data['annees_academiques'] ?? [
    ['id' => 1, 'libelle' => '2022-2023', 'date_debut' => '2022-09-01', 'date_fin' => '2023-08-31', 'active' => false],
    ['id' => 2, 'libelle' => '2023-2024', 'date_debut' => '2023-09-01', 'date_fin' => '2024-08-31', 'active' => false],
    ['id' => 3, 'libelle' => '2024-2025', 'date_debut' => '2024-09-01', 'date_fin' => '2025-08-31', 'active' => true], // Année active
    ['id' => 4, 'libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-08-31', 'active' => false],
];

// Si un formulaire est en mode modification, $annee_a_modifier serait passé
$annee_a_modifier = $data['annee_a_modifier'] ?? null;
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Années Académiques</h1>

    <section class="section-form admin-card">
        <h2 class="section-title"><?= $annee_a_modifier ? 'Modifier' : 'Ajouter'; ?> une Année Académique</h2>
        <form id="formAnneeAcademique" action="/admin/config/annee-academique/<?= $annee_a_modifier ? 'update/' . e($annee_a_modifier['id']) : 'create'; ?>" method="POST">
            <div class="form-group">
                <label for="libelle">Libellé :</label>
                <input type="text" id="libelle" name="libelle" value="<?= e($annee_a_modifier['libelle'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_debut">Date de Début :</label>
                <input type="date" id="date_debut" name="date_debut" value="<?= e($annee_a_modifier['date_debut'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_fin">Date de Fin :</label>
                <input type="date" id="date_fin" name="date_fin" value="<?= e($annee_a_modifier['date_fin'] ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons"><?= $annee_a_modifier ? 'save' : 'add'; ?></span>
                <?= $annee_a_modifier ? 'Enregistrer les modifications' : 'Ajouter l\'Année Académique'; ?>
            </button>
            <?php if ($annee_a_modifier): ?>
                <a href="/admin/config/annee-academique" class="btn btn-secondary-gray ml-md">Annuler</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Liste des Années Académiques</h2>
        <table class="data-table">
            <thead>
            <tr>
                <th>Libellé</th>
                <th>Date de Début</th>
                <th>Date de Fin</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($annees_academiques)): ?>
                <?php foreach ($annees_academiques as $annee): ?>
                    <tr class="<?= $annee['active'] ? 'active-row' : ''; ?>">
                        <td><?= e($annee['libelle']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($annee['date_debut']))); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($annee['date_fin']))); ?></td>
                        <td>
                            <?php if ($annee['active']): ?>
                                <span class="status-indicator status-healthy">Active</span>
                            <?php else: ?>
                                <span class="status-indicator status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <?php if (!$annee['active']): ?>
                                <form action="/admin/config/annee-academique/set-active/<?= e($annee['id']); ?>" method="POST" style="display:inline-block;">
                                    <button type="submit" class="btn-action activate-btn" title="Définir comme active">
                                        <span class="material-icons">check_circle_outline</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="/admin/config/annee-academique/edit/<?= e($annee['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/config/annee-academique/delete/<?= e($annee['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette année académique ?');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer">
                                    <span class="material-icons">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Aucune année académique enregistrée pour le moment.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript pour la gestion des formulaires et des actions si nécessaire.
        // Par exemple, gestion des messages de succès/erreur après soumission.

        const form = document.getElementById('formAnneeAcademique');
        if (form) {
            form.addEventListener('submit', function(event) {
                // Ici, vous pourriez ajouter des validations front-end supplémentaires si nécessaire
                // Par exemple, vérifier que date_debut est antérieure à date_fin.
                const dateDebut = document.getElementById('date_debut').value;
                const dateFin = document.getElementById('date_fin').value;

                if (new Date(dateDebut) >= new Date(dateFin)) {
                    alert('La date de début doit être antérieure à la date de fin.');
                    event.preventDefault(); // Empêche la soumission du formulaire
                }
            });
        }

        // Gestion de l'affichage des messages flash (si votre système en utilise)
        // Exemple simplifié, à adapter selon votre implémentation de messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            // Afficher le message (ex: avec une div temporaire ou un toast)
            // Vous aurez besoin d'un élément HTML pour cela, par exemple:
            // <div id="flash-message" class="alert success" style="display:none;"></div>
            // const flashDiv = document.getElementById('flash-message');
            // if (flashDiv) {
            //     flashDiv.textContent = flashMessage;
            //     flashDiv.style.display = 'block';
            //     setTimeout(() => flashDiv.style.display = 'none', 5000); // Masquer après 5 secondes
            // }
            console.log("Message Flash:", flashMessage);
            // Nettoyer le message flash après affichage (important pour ne pas le réafficher)
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour annee_academique.php */
    /* Réutilisation des classes de root.css et des conventions admin_module.css */

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
        background-color: var(--bg-secondary); /* Fond légèrement différent pour les sections */
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
    .form-group input[type="date"] {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
    }

    .form-group input:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
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
        white-space: nowrap; /* Empêche les boutons de passer à la ligne */
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
        font-size: var(--font-size-xl); /* Taille des icônes Material Icons */
    }

    .btn-action:hover {
        background-color: var(--primary-gray-light);
    }

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .btn-action.activate-btn { color: var(--primary-green); }
    .btn-action.activate-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 70px; /* Taille minimale pour uniformiser */
        text-align: center;
    }

    .status-healthy {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-inactive {
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }

    .active-row {
        background-color: rgba(16, 185, 129, 0.05); /* Fond légèrement teinté pour l'année active */
        font-weight: var(--font-weight-medium);
    }
</style>