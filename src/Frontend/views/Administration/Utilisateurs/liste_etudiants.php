<?php
// src/Frontend/views/Administration/Utilisateurs/liste_etudiants.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les étudiants (proviennent du contrôleur UtilisateurController)
// Ces données sont des exemples pour structurer la vue.
//

$etudiants_enregistres = $data['etudiants_enregistres'] ?? [
    ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean.dupont@etu.com', 'matricule_etudiant' => 'ETU-001', 'niveau_etude' => 'Master 2', 'status_compte' => 'Actif'],
    ['id' => 2, 'nom' => 'Curie', 'prenom' => 'Marie', 'email' => 'marie.curie@etu.com', 'matricule_etudiant' => 'ETU-002', 'niveau_etude' => 'Master 2', 'status_compte' => 'Inactif'],
    ['id' => 3, 'nom' => 'Voltaire', 'prenom' => 'François', 'email' => 'f.voltaire@etu.com', 'matricule_etudiant' => 'ETU-003', 'niveau_etude' => 'Licence 3', 'status_compte' => 'Actif'],
    ['id' => 4, 'nom' => 'Rousseau', 'prenom' => 'Sophie', 'email' => 'sophie.r@etu.com', 'matricule_etudiant' => 'ETU-004', 'niveau_etude' => 'Master 1', 'status_compte' => 'Bloqué'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion des Étudiants</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Liste des Étudiants</h2>
            <div class="action-buttons">
                <a href="/admin/utilisateurs/etudiant/create" class="btn btn-primary-blue">
                    <span class="material-icons">person_add</span>
                    Ajouter un Étudiant
                </a>
                <a href="/admin/utilisateurs/import-etudiants" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">cloud_upload</span>
                    Importer en Masse
                </a>
            </div>
        </div>

        <?php if (!empty($etudiants_enregistres)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Matricule</th>
                    <th>Niveau d'Étude</th>
                    <th>Statut Compte</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($etudiants_enregistres as $etudiant): ?>
                    <tr>
                        <td><?= e($etudiant['prenom']) . ' ' . e($etudiant['nom']); ?></td>
                        <td><?= e($etudiant['email']); ?></td>
                        <td><?= e($etudiant['matricule_etudiant']); ?></td>
                        <td><?= e($etudiant['niveau_etude']); ?></td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(e($etudiant['status_compte'])); ?>">
                                    <?= e($etudiant['status_compte']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <?php if ($etudiant['status_compte'] === 'Inactif'): ?>
                                <form action="/admin/utilisateurs/etudiant/activate/<?= e($etudiant['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir activer le compte de cet étudiant ?');">
                                    <button type="submit" class="btn-action activate-btn" title="Activer le compte">
                                        <span class="material-icons">person_add_alt_1</span>
                                    </button>
                                </form>
                            <?php elseif ($etudiant['status_compte'] === 'Actif'): ?>
                                <form action="/admin/utilisateurs/etudiant/deactivate/<?= e($etudiant['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver le compte de cet étudiant ?');">
                                    <button type="submit" class="btn-action deactivate-btn" title="Désactiver le compte">
                                        <span class="material-icons">person_off</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="/admin/utilisateurs/etudiant/edit/<?= e($etudiant['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/utilisateurs/etudiant/delete/<?= e($etudiant['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">
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
            <p class="text-center text-muted">Aucun étudiant enregistré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/utilisateurs/etudiant/create" class="btn btn-primary-blue">Ajouter le premier étudiant</a>
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
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour liste_etudiants.php */
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

    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        margin: 0;
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

    .btn-action.activate-btn { color: var(--primary-green); }
    .btn-action.activate-btn:hover { background-color: rgba(16, 185, 129, 0.1); }

    .btn-action.deactivate-btn { color: var(--accent-yellow); }
    .btn-action.deactivate-btn:hover { background-color: rgba(245, 158, 11, 0.1); }

    .btn-action.edit-btn { color: var(--primary-blue); }
    .btn-action.edit-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    /* Statuts d'étudiant */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

    .status-actif {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-inactif {
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }

    .status-bloqué { /* Pour compte bloqué suite à tentatives de connexion échouées ou pénalités */
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }
</style>