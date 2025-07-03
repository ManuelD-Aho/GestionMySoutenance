<?php
// src/Frontend/views/Administration/Utilisateurs/liste_utilisateurs.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les utilisateurs (proviennent du contrôleur UtilisateurController)
// Ces données sont des exemples pour structurer la vue.
//
//

$utilisateurs_enregistres = $data['utilisateurs_enregistres'] ?? [
    ['id' => 1, 'nom' => 'Admin', 'prenom' => 'Principal', 'email' => 'admin@univ.com', 'type_utilisateur' => 'Administrateur', 'groupe_principal' => 'GRP_ADMIN', 'status_compte' => 'Actif', 'identifiant_unique' => 'ADM-2025-0001'],
    ['id' => 2, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean.dupont@etu.com', 'type_utilisateur' => 'Étudiant', 'groupe_principal' => 'GRP_ETUDIANT', 'status_compte' => 'Actif', 'identifiant_unique' => 'ETU-2025-0001'],
    ['id' => 3, 'nom' => 'Martin', 'prenom' => 'Sophie', 'email' => 'sophie.martin@univ.com', 'type_utilisateur' => 'Enseignant', 'groupe_principal' => 'GRP_ENSEIGNANT', 'status_compte' => 'Actif', 'identifiant_unique' => 'ENS-2025-0001'],
    ['id' => 4, 'nom' => 'Curie', 'prenom' => 'Marie', 'email' => 'marie.curie@etu.com', 'type_utilisateur' => 'Étudiant', 'groupe_principal' => 'GRP_ETUDIANT', 'status_compte' => 'Inactif', 'identifiant_unique' => 'ETU-2025-0002'],
    ['id' => 5, 'nom' => 'Durand', 'prenom' => 'Claire', 'email' => 'claire.durand@admin.com', 'type_utilisateur' => 'Personnel Administratif', 'groupe_principal' => 'GRP_RS', 'status_compte' => 'Actif', 'identifiant_unique' => 'PERS-2025-0001'],
    ['id' => 6, 'nom' => 'Blocage', 'prenom' => 'Compte', 'email' => 'bloque@test.com', 'type_utilisateur' => 'Test', 'groupe_principal' => 'GRP_TEST', 'status_compte' => 'Bloqué', 'identifiant_unique' => 'TEST-2025-0001'],
];

// Options de filtrage
$types_utilisateur_filtre = $data['types_utilisateur_filtre'] ?? [
    'ALL' => 'Tous les types',
    'Administrateur' => 'Administrateur',
    'Étudiant' => 'Étudiant',
    'Enseignant' => 'Enseignant',
    'Personnel Administratif' => 'Personnel Administratif',
];

$statuts_compte_filtre = $data['statuts_compte_filtre'] ?? [
    'ALL' => 'Tous les statuts',
    'Actif' => 'Actif',
    'Inactif' => 'Inactif',
    'Bloqué' => 'Bloqué',
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion Globale des Utilisateurs</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Utilisateurs</h2>
        <form id="userFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_type">Type d'Utilisateur :</label>
                <select id="filter_type" name="type">
                    <?php foreach ($types_utilisateur_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['type'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_status">Statut du Compte :</label>
                <select id="filter_status" name="status">
                    <?php foreach ($statuts_compte_filtre as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['status'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_keyword">Recherche (Nom, Email, ID) :</label>
                <input type="text" id="filter_keyword" name="keyword" value="<?= e($_GET['keyword'] ?? ''); ?>" placeholder="Rechercher...">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/admin/utilisateurs/liste'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <div class="section-header">
            <h2 class="section-title">Liste de tous les Utilisateurs</h2>
            <div class="action-buttons">
                <a href="/admin/utilisateurs/generic/create" class="btn btn-primary-blue">
                    <span class="material-icons">person_add</span>
                    Ajouter un Utilisateur
                </a>
                <a href="/admin/utilisateurs/import-etudiants" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">cloud_upload</span>
                    Importer Étudiants
                </a>
            </div>
        </div>

        <?php if (!empty($utilisateurs_enregistres)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Identifiant Unique</th>
                    <th>Type</th>
                    <th>Groupe Principal</th>
                    <th>Statut Compte</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($utilisateurs_enregistres as $user): ?>
                    <tr>
                        <td><?= e($user['prenom']) . ' ' . e($user['nom']); ?></td>
                        <td><?= e($user['email']); ?></td>
                        <td><?= e($user['identifiant_unique']); ?></td>
                        <td><?= e($user['type_utilisateur']); ?></td>
                        <td><?= e($user['groupe_principal']); ?></td>
                        <td>
                                <span class="status-indicator status-<?= strtolower(e($user['status_compte'])); ?>">
                                    <?= e($user['status_compte']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <?php if ($user['status_compte'] === 'Inactif'): ?>
                                <form action="/admin/utilisateurs/activate/<?= e($user['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Activer ce compte ?');">
                                    <button type="submit" class="btn-action activate-btn" title="Activer le compte">
                                        <span class="material-icons">person_add_alt_1</span>
                                    </button>
                                </form>
                            <?php elseif ($user['status_compte'] === 'Actif'): ?>
                                <form action="/admin/utilisateurs/deactivate/<?= e($user['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Désactiver ce compte ?');">
                                    <button type="submit" class="btn-action deactivate-btn" title="Désactiver le compte">
                                        <span class="material-icons">person_off</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="/admin/utilisateurs/generic/edit/<?= e($user['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <button type="button" class="btn-action reset-password-btn" title="Réinitialiser le mot de passe"
                                    onclick="confirmAction('Réinitialisation Mot de Passe', 'Réinitialiser le mot de passe de <?= e($user['prenom']) . ' ' . e($user['nom']); ?> ? Un mot de passe temporaire sera généré.', '/admin/utilisateurs/reset-password/<?= e($user['id']); ?>');">
                                <span class="material-icons">vpn_key</span>
                            </button>
                            <form action="/admin/utilisateurs/delete/<?= e($user['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');">
                                <button type="submit" class="btn-action delete-btn" title="Supprimer">
                                    <span class="material-icons">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination-controls mt-lg text-center">
                <button class="btn btn-secondary-gray" disabled>Précédent</button>
                <span class="current-page">Page 1 de X</span>
                <button class="btn btn-secondary-gray">Suivant</button>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucun utilisateur enregistré pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/utilisateurs/generic/create" class="btn btn-primary-blue">Ajouter le premier utilisateur</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction générique pour confirmer les actions critiques (réutilisée)
        window.confirmAction = function(title, message, actionUrl) {
            if (confirm(message)) {
                console.log(`Action "${title}" déclenchée vers: ${actionUrl}`);
                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`${title} : Succès !`);
                            window.location.reload();
                        } else {
                            alert(`Erreur lors de ${title} : ` + (data.message || 'Erreur inconnue.'));
                        }
                    })
                    .catch(error => {
                        console.error(`Erreur AJAX lors de ${title}:`, error);
                        alert(`Une erreur de communication est survenue lors de ${title}.`);
                    });
            }
            return false;
        };

        // Logique pour la gestion des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }

        // Gestion du formulaire de filtre (réutilisé d'autres vues de liste)
        const userFilterForm = document.getElementById('userFilterForm');
        if (userFilterForm) {
            userFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(userFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/admin/utilisateurs/liste?${queryParams.toString()}`;
            });
        }
    });
</script>

<style>
    /* Styles spécifiques pour liste_utilisateurs.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1400px; /* Plus large pour afficher plus de colonnes */
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

    .action-buttons { /* Conteneur pour les boutons "Ajouter", "Importer" */
        display: flex;
        gap: var(--spacing-sm);
        flex-wrap: wrap; /* Permettre le passage à la ligne sur petits écrans */
        justify-content: flex-end;
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

    /* Filtres - réutilisés et adaptés */
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
        align-items: flex-end;
    }

    .form-group {
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
    .form-group select {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
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

    .btn-action.reset-password-btn { color: var(--accent-violet); }
    .btn-action.reset-password-btn:hover { background-color: rgba(139, 92, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    /* Statuts de compte (réutilisés) */
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

    .status-bloqué {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }

    /* Pagination */
    .pagination-controls button {
        margin: 0 var(--spacing-xs);
    }
    .pagination-controls .current-page {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }
</style>