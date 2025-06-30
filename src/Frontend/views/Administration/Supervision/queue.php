<?php
// src/Frontend/views/Administration/Supervision/queue.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les jobs de la file d'attente (proviennent du contrôleur QueueController)
// Ces données sont des exemples pour structurer la vue.
//
//

$queue_stats = $data['queue_stats'] ?? [
    'total_jobs' => 250,
    'jobs_pending' => 10,
    'jobs_failed' => 2,
    'jobs_completed_24h' => 150,
];

$jobs_en_attente = $data['jobs_en_attente'] ?? [
    ['id' => 1, 'type' => 'EMAIL_NOTIFICATION', 'status' => 'PENDING', 'created_at' => '2025-06-30 14:00:00', 'attempts' => 0],
    ['id' => 2, 'type' => 'BULLETIN_GENERATION', 'status' => 'PENDING', 'created_at' => '2025-06-30 14:05:00', 'attempts' => 0],
    ['id' => 3, 'type' => 'ARCHIVE_PV', 'status' => 'PENDING', 'created_at' => '2025-06-30 14:10:00', 'attempts' => 0],
];

$jobs_echoues = $data['jobs_echoues'] ?? [
    ['id' => 101, 'type' => 'EMAIL_NOTIFICATION', 'status' => 'FAILED', 'created_at' => '2025-06-29 10:00:00', 'last_attempt_at' => '2025-06-29 10:05:00', 'attempts' => 3, 'error_message' => 'Service SMTP non disponible.'],
    ['id' => 102, 'type' => 'BULLETIN_GENERATION', 'status' => 'FAILED', 'created_at' => '2025-06-29 11:30:00', 'last_attempt_at' => '2025-06-29 11:35:00', 'attempts' => 2, 'error_message' => 'Erreur de données étudiant (ID invalide).'],
];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Gestion de la File d'Attente (Queue)</h1>

    <section class="section-queue-stats admin-card">
        <h2 class="section-title">Statistiques de la File d'Attente</h2>
        <div class="stats-grid">
            <div class="dashboard-card stat-card">
                <h3>Total Jobs</h3>
                <p class="stat-value"><?= e($queue_stats['total_jobs']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <h3>Jobs en Attente</h3>
                <p class="stat-value"><?= e($queue_stats['jobs_pending']); ?></p>
                <p class="stat-change neutral"><span class="material-icons">pending_actions</span>En cours de traitement</p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <h3>Jobs Échoués</h3>
                <p class="stat-value"><?= e($queue_stats['jobs_failed']); ?></p>
                <p class="stat-change negative"><span class="material-icons">error</span>À vérifier</p>
            </div>
            <div class="dashboard-card stat-card">
                <h3>Jobs Terminés (24h)</h3>
                <p class="stat-value"><?= e($queue_stats['jobs_completed_24h']); ?></p>
                <p class="stat-change positive"><span class="material-icons">check_circle</span>Traités récemment</p>
            </div>
        </div>
        <div class="text-center mt-lg">
            <button id="clearFailedJobsBtn" class="btn btn-accent-red" onclick="confirmAction('Vider les Jobs Échoués', 'Êtes-vous sûr de vouloir supprimer tous les jobs échoués ?', '/admin/supervision/queue/clear-failed');">
                <span class="material-icons">delete_sweep</span> Vider les Jobs Échoués
            </button>
            <button id="retryAllFailedJobsBtn" class="btn btn-primary-blue ml-md" onclick="confirmAction('Relancer Tous les Jobs Échoués', 'Êtes-vous sûr de vouloir relancer toutes les tâches échouées ?', '/admin/supervision/queue/retry-all-failed');">
                <span class="material-icons">refresh</span> Relancer Tous les Échoués
            </button>
        </div>
    </section>

    <section class="section-jobs-list admin-card mt-xl">
        <h2 class="section-title">Jobs en Attente de Traitement</h2>
        <?php if (!empty($jobs_en_attente)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID Job</th>
                    <th>Type de Tâche</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th>Tentatives</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($jobs_en_attente as $job): ?>
                    <tr class="job-status-<?= e(strtolower($job['status'])); ?>">
                        <td><?= e($job['id']); ?></td>
                        <td><?= e($job['type']); ?></td>
                        <td><span class="status-indicator status-info"><?= e($job['status']); ?></span></td>
                        <td><?= e(date('d/m/Y H:i:s', strtotime($job['created_at']))); ?></td>
                        <td><?= e($job['attempts']); ?></td>
                        <td class="actions">
                            <span class="text-muted">En attente</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Aucune tâche en attente dans la file d'attente.</p>
        <?php endif; ?>
    </section>

    <section class="section-jobs-list admin-card mt-xl">
        <h2 class="section-title">Jobs Échoués</h2>
        <?php if (!empty($jobs_echoues)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID Job</th>
                    <th>Type de Tâche</th>
                    <th>Échoué le</th>
                    <th>Tentatives</th>
                    <th>Message d'Erreur</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($jobs_echoues as $job): ?>
                    <tr class="job-status-failed">
                        <td><?= e($job['id']); ?></td>
                        <td><?= e($job['type']); ?></td>
                        <td><?= e(date('d/m/Y H:i:s', strtotime($job['last_attempt_at']))); ?></td>
                        <td><?= e($job['attempts']); ?></td>
                        <td class="log-message-cell" title="<?= e($job['error_message']); ?>">
                            <?= e(mb_strimwidth($job['error_message'], 0, 80, '...')); ?>
                        </td>
                        <td class="actions">
                            <button type="button" class="btn-action retry-job-btn" title="Relancer ce job"
                                    onclick="confirmAction('Relancer le Job', 'Êtes-vous sûr de vouloir relancer le job <?= e($job['id']); ?> (<?= e($job['type']); ?>) ?', '/admin/supervision/queue/retry-job/<?= e($job['id']); ?>');">
                                <span class="material-icons">replay</span>
                            </button>
                            <button type="button" class="btn-action delete-btn" title="Supprimer ce job"
                                    onclick="confirmAction('Supprimer le Job', 'Êtes-vous sûr de vouloir supprimer le job <?= e($job['id']); ?> (<?= e($job['type']); ?>) ?', '/admin/supervision/queue/delete-job/<?= e($job['id']); ?>');">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Aucun job échoué à afficher.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction générique pour confirmer les actions critiques
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
                            window.location.reload(); // Recharger pour refléter les changements
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
    /* Styles spécifiques pour queue.php */
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

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* Grille de statistiques (réutilisation des styles du dashboard) */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }

    .dashboard-card { /* Réutilisé du dashboard_admin */
        background-color: var(--primary-white);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--border-light);
    }
    .dashboard-card .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: var(--spacing-sm);
    }
    .dashboard-card .stat-label {
        font-size: var(--font-size-lg);
        color: var(--text-secondary);
        font-weight: var(--font-weight-medium);
        text-align: left;
        flex-grow: 1;
    }
    .dashboard-card .stat-icon {
        width: 48px; height: 48px; border-radius: var(--border-radius-full);
        display: flex; align-items: center; justify-content: center;
        font-size: var(--font-size-2xl); color: var(--text-white);
    }
    /* Couleurs des icônes de statistiques */
    .icon-bg-orange { background-color: var(--accent-yellow-light); }
    .icon-bg-blue { background-color: var(--primary-blue-light); }
    .icon-bg-green { background-color: var(--primary-green-light); }
    .icon-bg-red { background-color: var(--accent-red-light); }
    .icon-bg-violet { background-color: var(--accent-violet-light); }

    .dashboard-card .stat-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-top: var(--spacing-sm);
        width: 100%;
        text-align: center;
    }
    .dashboard-card .stat-change {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-top: var(--spacing-xs);
        width: 100%;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
    }
    .dashboard-card .stat-change.positive { color: var(--success-color); }
    .dashboard-card .stat-change.negative { color: var(--error-color); }
    .dashboard-card .stat-change.neutral { color: var(--info-color); }

    /* Boutons d'action globale pour la queue */
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

    .btn-accent-red {
        color: var(--text-white);
        background-color: var(--accent-red);
    }

    .btn-accent-red:hover {
        background-color: var(--accent-red-dark);
        box-shadow: var(--shadow-sm);
    }

    .ml-md { margin-left: var(--spacing-md); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-xl { margin-top: var(--spacing-xl); }

    /* Tableaux de données - réutilisés */
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

    .btn-action.retry-job-btn { color: var(--primary-blue); }
    .btn-action.retry-job-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.delete-btn { color: var(--accent-red); }
    .btn-action.delete-btn:hover { background-color: rgba(239, 68, 68, 0.1); }

    /* Statuts spécifiques des jobs */
    .job-status-pending { /* Base pour toutes les lignes */ }
    .job-status-failed {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }
    .job-status-failed:hover {
        background-color: var(--accent-red); /* Un peu plus foncé au survol */
        color: var(--text-white);
    }

    .status-indicator { /* Réutilisé des autres vues */
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }
    .status-info { /* Pour PENDING */
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .log-message-cell { /* Réutilisé de journaux_audit.php */
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: help;
    }
    .log-message-cell:hover {
        white-space: normal;
        overflow: visible;
        max-width: none;
        position: relative;
        z-index: 2;
        background-color: var(--primary-white);
        box-shadow: var(--shadow-md);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
</style>