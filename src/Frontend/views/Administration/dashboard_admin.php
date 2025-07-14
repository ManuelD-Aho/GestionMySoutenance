<?php
// /src/Frontend/views/Administration/dashboard_admin.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

// Données passées par AdminDashboardController
$stats = $data['stats'] ?? [];
$alerts = $data['alerts'] ?? [];
$shortcuts = $data['shortcuts'] ?? [];
$system_info = $data['system_info'] ?? [];
?>

<div class="space-y-6">
    <!-- Titre et résumé -->
    <div>
        <h1 class="text-3xl font-bold">Tableau de Bord Administrateur</h1>
        <p class="text-base-content/70">Vue d'ensemble de l'activité et de la santé du système.</p>
    </div>

    <!-- Alertes Système -->
    <?php if (!empty($alerts)): ?>
        <div class="space-y-3">
            <?php foreach ($alerts as $alert): ?>
                <div role="alert" class="alert alert-<?= e($alert['type']) ?> shadow-md">
                    <span class="material-icons"><?= e($alert['type'] === 'error' ? 'error' : 'warning') ?></span>
                    <div>
                        <h3 class="font-bold"><?= e(ucfirst($alert['type'])) ?></h3>
                        <div class="text-xs"><?= e($alert['message']) ?></div>
                    </div>
                    <?php if (isset($alert['action'])): ?>
                        <a href="<?= e($alert['action']) ?>" class="btn btn-sm btn-outline">Voir</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Grille de Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Utilisateurs Actifs -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div class="stat-title">Utilisateurs Actifs</div>
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-12">
                            <span class="material-icons">group</span>
                        </div>
                    </div>
                </div>
                <div class="stat-value text-primary"><?= e($stats['utilisateurs']['actif'] ?? 0) ?></div>
                <div class="stat-desc">Sur un total de <?= e($stats['utilisateurs']['total'] ?? 0) ?></div>
            </div>
        </div>
        <!-- Rapports en attente -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div class="stat-title">Rapports en Attente</div>
                    <div class="avatar placeholder">
                        <div class="bg-secondary text-secondary-content rounded-full w-12">
                            <span class="material-icons">hourglass_top</span>
                        </div>
                    </div>
                </div>
                <div class="stat-value text-secondary"><?= e($stats['rapports']['En Commission'] ?? 0) ?></div>
                <div class="stat-desc text-secondary"><?= e($stats['rapports']['Soumis'] ?? 0) ?> à vérifier</div>
            </div>
        </div>
        <!-- Tâches en échec -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div class="stat-title">Tâches en Échec</div>
                    <div class="avatar placeholder">
                        <div class="bg-error text-error-content rounded-full w-12">
                            <span class="material-icons">error_outline</span>
                        </div>
                    </div>
                </div>
                <div class="stat-value text-error"><?= e($stats['queue']['failed'] ?? 0) ?></div>
                <div class="stat-desc text-error">Nécessite une intervention</div>
            </div>
        </div>
        <!-- Infos Système -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div class="stat-title">Info Système</div>
                    <div class="avatar placeholder">
                        <div class="bg-info text-info-content rounded-full w-12">
                            <span class="material-icons">info</span>
                        </div>
                    </div>
                </div>
                <div class="stat-value text-info"><?= e($system_info['version'] ?? 'N/A') ?></div>
                <div class="stat-desc">Environnement: <?= e(strtoupper($system_info['environment'] ?? 'N/A')) ?></div>
            </div>
        </div>
    </div>

    <!-- Raccourcis rapides -->
    <div>
        <h2 class="text-xl font-bold mb-4">Accès Rapide</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($shortcuts as $shortcut): ?>
                <a href="<?= e($shortcut['url']) ?>" class="card bg-base-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="card-body items-center text-center">
                        <span class="material-icons text-4xl text-primary mb-2"><?= e($shortcut['icon']) ?></span>
                        <h3 class="card-title text-base"><?= e($shortcut['title']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>