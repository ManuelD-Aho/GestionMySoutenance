<?php
// src/Frontend/views/Administration/Fichier/list_files.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les fichiers (proviennent du contrôleur FichierController)
// En production, ces données seraient récupérées depuis la base de données via ServiceFichierInterface.
//
//

$fichiers_enregistres = $data['fichiers_enregistres'] ?? [
    ['id' => 1, 'nom' => 'Rapport_Fin_Etude_ETU-2024-001.pdf', 'type_mime' => 'application/pdf', 'taille' => '2.5 Mo', 'date_upload' => '2025-06-15 10:00:00', 'path' => '/uploads/rapports/2025/rapport_001.pdf'],
    ['id' => 2, 'nom' => 'Attestation_Scolarite_Jean_Dupont.pdf', 'type_mime' => 'application/pdf', 'taille' => '0.1 Mo', 'date_upload' => '2025-06-20 14:30:00', 'path' => '/generated/attestations/attest_jean.pdf'],
    ['id' => 3, 'nom' => 'Photo_Profil_Marie_Curie.jpg', 'type_mime' => 'image/jpeg', 'taille' => '0.8 Mo', 'date_upload' => '2025-06-22 09:15:00', 'path' => '/uploads/profils/marie_curie.jpg'],
    ['id' => 4, 'nom' => 'PV_Session_Validation_Juin.pdf', 'type_mime' => 'application/pdf', 'taille' => '1.2 Mo', 'date_upload' => '2025-06-25 11:45:00', 'path' => '/generated/pvs/pv_juin.pdf'],
];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Fichiers</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Fichiers Enregistrés</h2>
            <a href="/admin/fichiers/upload" class="btn btn-primary-blue">
                <span class="material-icons">upload_file</span>
                Télécharger un Fichier
            </a>
        </div>

        <?php if (!empty($fichiers_enregistres)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Nom du Fichier</th>
                    <th>Type MIME</th>
                    <th>Taille</th>
                    <th>Date d'Upload</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($fichiers_enregistres as $fichier): ?>
                    <tr>
                        <td><?= e($fichier['nom']); ?></td>
                        <td><?= e($fichier['type_mime']); ?></td>
                        <td><?= e($fichier['taille']); ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($fichier['date_upload']))); ?></td>
                        <td class="actions">
                            <a href="<?= e($fichier['path']); ?>" download="<?= e($fichier['nom']); ?>" class="btn-action download-btn" title="Télécharger">
                                <span class="material-icons">download</span>
                            </a>
                            <form action="/admin/fichiers/delete/<?= e($fichier['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ? Cette action est irréversible.');">
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
            <p class="text-center text-muted">Aucun fichier n'est enregistré dans le système pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/fichiers/upload" class="btn btn-primary-blue">Commencer l'Upload</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour la gestion des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            // Vous pouvez ajouter une logique d'affichage de toast ou alerte ici
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour list_files.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
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

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        margin: 0; /* Réinitialiser les marges par défaut si déjà définies */
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
        text-decoration: none; /* Pour les liens stylisés en bouton */
    }

    .btn-primary-blue {
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

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
        text-decoration: none; /* Pour les liens */
    }

    .btn-action:hover {
        background-color: var(--primary-gray-light);
    }

    .btn-action.download-btn { color: var(--primary-green); }
    .btn-action.download-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }