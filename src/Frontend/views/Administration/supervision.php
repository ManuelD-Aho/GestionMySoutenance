<?php
// /src/Frontend/views/Administration/supervision.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$title = $title ?? 'Supervision du Système';
$audit_logs = $data['audit_logs'] ?? [];
$async_tasks = $data['async_tasks'] ?? [];
$error_log_content = $data['error_log_content'] ?? 'Fichier de log non trouvé ou vide.';
?>

<div class="space-y-6">
    <h1 class="text-3xl font-bold"><?= e($title) ?></h1>

    <div role="tablist" class="tabs tabs-lifted">
        <!-- Onglet Journal d'Audit -->
        <input type="radio" name="supervision_tabs" role="tab" class="tab" aria-label="Journal d'Audit" checked />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Piste d'Audit des Actions Sensibles</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Entité Concernée</th>
                        <th>Détails</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($audit_logs as $log): ?>
                        <tr>
                            <td><?= e(date('d/m/Y H:i:s', strtotime($log['date_action']))) ?></td>
                            <td><?= e($log['numero_utilisateur']) ?></td>
                            <td><span class="badge badge-info"><?= e($log['id_action']) ?></span></td>
                            <td><?= e($log['type_entite_concernee'] . ' - ' . $log['id_entite_concernee']) ?></td>
                            <td><pre class="text-xs max-w-xs truncate"><?= e($log['details_action']) ?></pre></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Onglet Tâches Asynchrones -->
        <input type="radio" name="supervision_tabs" role="tab" class="tab" aria-label="File d'Attente" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Gestion de la File d'Attente (Queue)</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Job</th>
                        <th>Statut</th>
                        <th>Tentatives</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($async_tasks as $task): ?>
                        <tr class="hover">
                            <td><?= e($task['job_name']) ?></td>
                            <td><span class="badge badge-<?= e(['pending' => 'info', 'processing' => 'warning', 'completed' => 'success', 'failed' => 'error'][$task['status']]) ?>"><?= e($task['status']) ?></span></td>
                            <td><?= e($task['attempts']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($task['created_at']))) ?></td>
                            <td>
                                <?php if ($task['status'] === 'failed'): ?>
                                    <button class="btn btn-xs btn-outline btn-success">Relancer</button>
                                    <button class="btn btn-xs btn-outline btn-error">Supprimer</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Onglet Logs PHP -->
        <input type="radio" name="supervision_tabs" role="tab" class="tab" aria-label="Logs Erreurs" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-semibold mb-4">Dernières Erreurs PHP</h2>
            <div class="mockup-code bg-neutral text-neutral-content">
                <pre><code><?= e($error_log_content) ?></code></pre>
            </div>
        </div>
    </div>
</div>