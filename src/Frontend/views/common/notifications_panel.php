<?php
// /src/Frontend/views/common/notifications_panel.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

$notifications = $data['notifications'] ?? [];
$pageTitle = 'Toutes les Notifications';
?>

<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-6"><?= e($pageTitle) ?></h1>

    <!-- Filtres et Actions -->
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <div class="flex flex-wrap items-end gap-4">
                <!-- Filtres -->
                <div class="form-control">
                    <label class="label"><span class="label-text">Statut</span></label>
                    <select class="select select-bordered">
                        <option>Toutes</option>
                        <option>Non lues</option>
                        <option>Lues</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Type</span></label>
                    <select class="select select-bordered">
                        <option>Tous</option>
                        <option>Info</option>
                        <option>Succès</option>
                        <option>Avertissement</option>
                        <option>Erreur</option>
                    </select>
                </div>
                <!-- Actions -->
                <div class="flex-grow"></div>
                <button class="btn btn-ghost">Tout marquer comme lu</button>
                <button class="btn btn-error btn-outline">Supprimer les lues</button>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="space-y-3">
        <?php if (empty($notifications)): ?>
            <div class="text-center p-8 text-base-content/60">
                <span class="material-icons text-5xl">notifications_off</span>
                <p class="mt-4">Vous n'avez aucune notification.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif):
                $isRead = $notif['read'] ?? false;
                $type = $notif['type'] ?? 'info';
                $icon = ['success' => 'check_circle', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'][$type];
                $colorClass = ['success' => 'border-success', 'error' => 'border-error', 'warning' => 'border-warning', 'info' => 'border-info'][$type];
                ?>
                <div class="alert shadow-sm <?= $isRead ? 'opacity-60' : $colorClass ?>">
                    <span class="material-icons"><?= $icon ?></span>
                    <div class="flex-1">
                        <p><?= e($notif['message']) ?></p>
                        <small class="opacity-70"><?= e(date('d/m/Y H:i', strtotime($notif['date']))) ?></small>
                    </div>
                    <div class="flex-none">
                        <?php if (!$isRead): ?>
                            <button class="btn btn-sm btn-ghost" title="Marquer comme lu"><span class="material-icons">drafts</span></button>
                        <?php endif; ?>
                        <a href="<?= e($notif['link'] ?? '#') ?>" class="btn btn-sm btn-ghost" title="Voir les détails"><span class="material-icons">visibility</span></a>
                        <button class="btn btn-sm btn-ghost" title="Supprimer"><span class="material-icons">delete</span></button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination (si nécessaire) -->
    <div class="join mt-8 flex justify-center">
        <button class="join-item btn">«</button>
        <button class="join-item btn">Page 1</button>
        <button class="join-item btn btn-disabled">...</button>
        <button class="join-item btn">»</button>
    </div>
</div>