<?php
// src/Frontend/views/Administration/Supervision/logs.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les logs (proviennent du contrôleur LoggerController ou SupervisionController)
// Ces données sont des exemples pour structurer la vue.
//
//

$logs_systeme = $data['logs_systeme'] ?? [
    ['id' => 1, 'timestamp' => '2025-06-30 14:05:30', 'level' => 'ERROR', 'message' => 'Failed to connect to database: Access denied for user.', 'source' => 'DB_CONNECTION', 'context' => ['user' => 'app_user'], 'stack_trace' => '...'],
    ['id' => 2, 'timestamp' => '2025-06-30 14:02:15', 'level' => 'WARNING', 'message' => 'Upload failed: File type not allowed for document.pdf.', 'source' => 'FILE_UPLOAD', 'context' => ['file' => 'document.pdf'], 'stack_trace' => null],
    ['id' => 3, 'timestamp' => '2025-06-30 13:58:00', 'level' => 'INFO', 'message' => 'User ADM-2025-0001 logged in successfully.', 'source' => 'AUTH', 'context' => ['user_id' => 'ADM-2025-0001'], 'stack_trace' => null],
    ['id' => 4, 'timestamp' => '2025-06-30 13:55:10', 'level' => 'CRITICAL', 'message' => 'Queue worker stopped unexpectedly.', 'source' => 'QUEUE_MANAGER', 'context' => ['worker_id' => 'worker-001'], 'stack_trace' => '...'],
    ['id' => 5, 'timestamp' => '2025-06-30 13:50:05', 'level' => 'DEBUG', 'message' => 'Data fetched for dashboard.', 'source' => 'DASHBOARD_API', 'context' => ['query_time' => '120ms'], 'stack_trace' => null],
];

// Options de filtrage pour les niveaux de log
$log_levels = $data['log_levels'] ?? [
    'ALL' => 'Tous les niveaux',
    'DEBUG' => 'Debug',
    'INFO' => 'Info',
    'WARNING' => 'Avertissement',
    'ERROR' => 'Erreur',
    'CRITICAL' => 'Critique',
];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Journaux du Système (Logs)</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Logs</h2>
        <form id="logFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_level">Niveau de Log :</label>
                <select id="filter_level" name="level">
                    <?php foreach ($log_levels as $code => $libelle): ?>
                        <option value="<?= e($code); ?>"
                            <?= (($_GET['level'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_message">Message / Mot-clé :</label>
                <input type="text" id="filter_message" name="message" value="<?= e($_GET['message'] ?? ''); ?>" placeholder="Rechercher dans les messages...">
            </div>
            <div class="form-group">
                <label for="filter_date_debut">Date de Début :</label>
                <input type="date" id="filter_date_debut" name="date_debut" value="<?= e($_GET['date_debut'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="filter_date_fin">Date de Fin :</label>
                <input type="date" id="filter_date_fin" name="date_fin" value="<?= e($_GET['date_fin'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer les Filtres
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/admin/supervision/logs'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <h2 class="section-title">Dernières Entrées de Log</h2>
        <?php if (!empty($logs_systeme)): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Horodatage</th>
                    <th>Niveau</th>
                    <th>Message</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($logs_systeme as $log): ?>
                    <tr class="log-level-<?= e(strtolower($log['level'])); ?>">
                        <td><?= e(date('d/m/Y H:i:s', strtotime($log['timestamp']))); ?></td>
                        <td><span class="log-level-badge log-level-badge-<?= e(strtolower($log['level'])); ?>"><?= e($log['level']); ?></span></td>
                        <td class="log-message-cell" title="<?= e($log['message']); ?>">
                            <?= e(mb_strimwidth($log['message'], 0, 100, '...')); ?>
                        </td>
                        <td><?= e($log['source']); ?></td>
                        <td class="actions">
                            <?php if (!empty($log['stack_trace']) || !empty($log['context'])): ?>
                                <button type="button" class="btn-action view-details-btn" title="Voir les détails" data-log-id="<?= e($log['id']); ?>">
                                    <span class="material-icons">info</span>
                                </button>
                            <?php endif; ?>
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
            <p class="text-center text-muted">Aucune entrée de log ne correspond aux critères.</p>
        <?php endif; ?>
    </section>
</div>

<div id="logDetailsModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Détails du Log</h2>
        <div id="modalLogContent">
            <p><strong>Horodatage :</strong> <span id="modalTimestamp"></span></p>
            <p><strong>Niveau :</strong> <span id="modalLevel"></span></p>
            <p><strong>Message :</strong> <span id="modalMessage"></span></p>
            <p><strong>Source :</strong> <span id="modalSource"></span></p>
            <p><strong>Contexte :</strong> <pre><code id="modalContext"></code></pre></p>
            <p><strong>Stack Trace :</strong> <pre><code id="modalStackTrace"></code></pre></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logFilterForm = document.getElementById('logFilterForm');
        const logDetailsModal = document.getElementById('logDetailsModal');
        const closeButton = logDetailsModal ? logDetailsModal.querySelector('.close-button') : null;
        const viewDetailsButtons = document.querySelectorAll('.view-details-btn');

        if (logFilterForm) {
            logFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(logFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') { // Exclure 'ALL' pour ne pas polluer l'URL
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/admin/supervision/logs?${queryParams.toString()}`;
            });
        }

        // Gestion du modal de détails
        if (logDetailsModal && closeButton) {
            closeButton.addEventListener('click', () => logDetailsModal.style.display = 'none');
            window.addEventListener('click', (event) => {
                if (event.target === logDetailsModal) {
                    logDetailsModal.style.display = 'none';
                }
            });

            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const logId = this.dataset.logId;
                    // En production, vous feriez un appel AJAX pour récupérer les détails complets
                    // car les logs peuvent être très volumineux (surtout la stack trace).
                    // Pour la démo, nous utilisons les données fictives PHP
                    const allLogs = <?= json_encode($logs_systeme); ?>;
                    const log = allLogs.find(l => l.id == logId);

                    if (log) {
                        document.getElementById('modalTimestamp').textContent = new Date(log.timestamp).toLocaleString();
                        document.getElementById('modalLevel').textContent = log.level;
                        document.getElementById('modalMessage').textContent = log.message;
                        document.getElementById('modalSource').textContent = log.source;
                        document.getElementById('modalContext').textContent = log.context ? JSON.stringify(log.context, null, 2) : 'N/A';
                        document.getElementById('modalStackTrace').textContent = log.stack_trace || 'N/A';
                        logDetailsModal.style.display = 'block';
                    } else {
                        alert('Détails du log non trouvés.');
                    }
                });
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
    /* Styles spécifiques pour logs.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .admin-module-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1400px; /* Plus large pour les logs */
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
    .form-group input[type="date"],
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

    .filter-form button {
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

    /* Tableaux de données - réutilisation */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: var(--spacing-md);
        font-size: var(--font-size-sm); /* Plus petit pour les logs détaillés */
    }

    .data-table th,
    .data-table td {
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-light);
        text-align: left;
        color: var(--text-primary);
        vertical-align: top;
    }

    .data-table th {
        background-color: var(--bg-secondary);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .data-table tbody tr:nth-child(even) {
        background-color: var(--primary-gray-light);
    }

    .data-table tbody tr:hover {
        background-color: var(--border-medium);
        transition: background-color var(--transition-fast);
    }

    .log-level-badge {
        padding: 0.2em 0.6em;
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-bold);
        display: inline-block;
        min-width: 60px;
        text-align: center;
    }
    .log-level-badge-debug { background-color: #e0f2f7; color: #0288d1; }
    .log-level-badge-info { background-color: var(--primary-blue-light); color: var(--primary-blue-dark); }
    .log-level-badge-warning { background-color: var(--accent-yellow-light); color: var(--accent-yellow-dark); }
    .log-level-badge-error { background-color: var(--accent-red-light); color: var(--accent-red-dark); }
    .log-level-badge-critical { background-color: #b71c1c; color: var(--text-white); } /* Rouge plus foncé pour critique */

    .log-message-cell {
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: help; /* Indique que c'est cliquable/survolable pour plus d'info */
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

    /* Modal Styles (simple, peut être amélioré avec une bibliothèque JS de modale) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: var(--z-modal); /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: var(--bg-overlay); /* Black w/ opacity */
        padding-top: 60px; /* Location of the box */
    }

    .modal-content {
        background-color: var(--bg-primary);
        margin: 5% auto; /* 15% from the top and centered */
        padding: var(--spacing-xl);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-lg);
        width: 80%; /* Could be more responsive */
        box-shadow: var(--shadow-2xl);
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-content h2 {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-light);
        padding-bottom: var(--spacing-sm);
    }

    .modal-content p {
        margin-bottom: var(--spacing-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
    }

    .modal-content strong {
        color: var(--primary-blue-dark);
    }

    .modal-content pre {
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
        padding: var(--spacing-md);
        border-radius: var(--border-radius-sm);
        overflow-x: auto;
        white-space: pre-wrap; /* Permet le retour à la ligne pour le code */
        word-wrap: break-word;
        font-size: var(--font-size-sm);
        color: var(--text-primary);
    }

    .close-button {
        color: var(--text-secondary);
        font-size: var(--font-size-3xl);
        position: absolute;
        top: var(--spacing-md);
        right: var(--spacing-md);
        cursor: pointer;
        transition: color var(--transition-fast);
    }

    .close-button:hover,
    .close-button:focus {
        color: var(--text-primary);
    }


    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }

    /* Pagination */
    .pagination-controls button {
        margin: 0 var(--spacing-xs);
    }
    .pagination-controls .current-page {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }