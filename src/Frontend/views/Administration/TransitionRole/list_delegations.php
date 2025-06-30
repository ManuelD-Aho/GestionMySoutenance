<?php
// src/Frontend/views/Administration/TransitionRole/list_delegations.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les délégations (proviennent du contrôleur TransitionRoleController)
// Ces données sont des exemples pour structurer la vue.
//

$delegations_enregistrees = $data['delegations_enregistrees'] ?? [
    ['id' => 1, 'delegant_nom' => 'Administrateur Principal', 'delegue_nom' => 'Agent Conformité (AC-001)', 'permissions_libelles' => 'Vérifier Conformité Rapport, Gérer Fichiers', 'date_debut' => '2025-07-01', 'date_fin' => '2025-07-15', 'status' => 'Active'],
    ['id' => 2, 'delegant_nom' => 'Responsable Scolarité (RS-001)', 'delegue_nom' => 'Assistant Scolarité (AS-001)', 'permissions_libelles' => 'Gérer Inscriptions', 'date_debut' => '2025-06-01', 'date_fin' => '2025-06-30', 'status' => 'Expirée'],
    ['id' => 3, 'delegant_nom' => 'Membre Commission A', 'delegue_nom' => 'Membre Commission B', 'permissions_libelles' => 'Voter sur Rapport, Rédiger PV', 'date_debut' => '2025-06-25', 'date_fin' => '2025-07-05', 'status' => 'Active'],
];
?>

<div class="admin-module-container">
    <h1 class="admin-title">Liste des Délégations</h1>

    <section class="section-list admin-card">
        <div class="section-header">
            <h2 class="section-title">Délégations de Responsabilités</h2>
            <a href="/admin/transition-role/delegations/create" class="btn btn-primary-blue">
                <span class="material-icons">add_circle</span>
                Créer une Nouvelle Délégation
            </a>
        </div>

        <?php if (!empty($delegations_enregistrees)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Délégant</th>
                    <th>Délégué</th>
                    <th>Permissions Déléguées</th>
                    <th>Période</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($delegations_enregistrees as $delegation): ?>
                    <tr class="delegation-status-<?= e(strtolower($delegation['status'])); ?>">
                        <td><?= e($delegation['delegant_nom']); ?></td>
                        <td><?= e($delegation['delegue_nom']); ?></td>
                        <td title="<?= e($delegation['permissions_libelles']); ?>">
                            <?= e(mb_strimwidth($delegation['permissions_libelles'], 0, 50, '...')); ?>
                        </td>
                        <td><?= e(date('d/m/Y', strtotime($delegation['date_debut']))) . ' - ' . e(date('d/m/Y', strtotime($delegation['date_fin']))); ?></td>
                        <td>
                                <span class="status-indicator status-<?= e(strtolower($delegation['status'])); ?>">
                                    <?= e($delegation['status']); ?>
                                </span>
                        </td>
                        <td class="actions">
                            <?php if ($delegation['status'] === 'Active'): ?>
                                <button type="button" class="btn-action terminate-btn" title="Terminer la délégation"
                                        onclick="confirmAction('Terminer la Délégation', 'Êtes-vous sûr de vouloir terminer cette délégation maintenant ?', '/admin/transition-role/delegations/terminate/<?= e($delegation['id']); ?>');">
                                    <span class="material-icons">event_busy</span>
                                </button>
                            <?php endif; ?>
                            <a href="/admin/transition-role/delegations/edit/<?= e($delegation['id']); ?>" class="btn-action edit-btn" title="Modifier">
                                <span class="material-icons">edit</span>
                            </a>
                            <form action="/admin/transition-role/delegations/delete/<?= e($delegation['id']); ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette délégation ?');">
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
            <p class="text-center text-muted">Aucune délégation enregistrée pour le moment.</p>
            <div class="text-center mt-lg">
                <a href="/admin/transition-role/delegations/create" class="btn btn-primary-blue">Créer la première délégation</a>
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

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour list_delegations.php */
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

    .btn-action.terminate-btn { color: var(--accent-yellow); } /* Pour terminer une délégation */
    .btn-action.terminate-btn:hover { background-color: rgba(245, 158, 11, 0.1); }

    /* Indicateurs de statut spécifiques aux délégations */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

    .status-active {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-expirée, .status-terminated { /* Pour les délégations terminées ou expirées */
        background-color: var(--border-medium);
        color: var(--text-secondary);
    }

    .delegation-status-active { /* Ligne pour les délégations actives */
        /* background-color: rgba(16, 185, 129, 0.05); */
        font-weight: var(--font-weight-medium);
    }

    .delegation-status-expirée, .delegation-status-terminated {
        opacity: 0.8; /* Légèrement grisé */
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-lg { margin-top: var(--spacing-lg); }