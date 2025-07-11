<?php
/**
 * Vue du tableau de bord de l'administrateur.
 *
 * Affiche une vue d'ensemble de l'état du système, incluant des statistiques clés,
 * des liens rapides vers les sections de gestion, et des alertes importantes.
 *
 * @var string $title Le titre de la page.
 * @var array $stats Données statistiques agrégées fournies par le contrôleur.
 * @var array $alerts Alertes système à afficher.
 */

// Préparation des données pour une utilisation plus facile dans la vue
$userStats = $stats['utilisateurs'] ?? [];
$reportStats = $stats['rapports'] ?? [];
$queueStats = $stats['queue'] ?? [];
$activityStats = $stats['activite_recente'] ?? [];
$claimStats = $stats['reclamations'] ?? [];

// Calculs pour les barres de progression
$totalUsers = $userStats['total'] ?? 1;
$activeUsersPercent = $totalUsers > 0 ? round(($userStats['actif'] ?? 0) / $totalUsers * 100) : 0;

$totalReports = array_sum($reportStats);
$validatedReportsPercent = $totalReports > 0 ? round(($reportStats['Validé'] ?? 0) / $totalReports * 100) : 0;

$totalQueue = array_sum($queueStats);
$failedQueuePercent = $totalQueue > 0 ? round(($queueStats['failed'] ?? 0) / $totalQueue * 100) : 0;
?>

<div class="container mx-auto p-4 md:p-6 lg:p-8">

    <!-- Titre de la page -->
    <h1 class="text-3xl font-bold text-base-content mb-6 animate-fade-in-down">
        Tableau de Bord Administrateur
    </h1>

    <!-- Alertes Système -->
    <?php if (!empty($alerts)): ?>
        <div class="space-y-4 mb-6 animate-fade-in">
            <?php foreach ($alerts as $alert): ?>
                <div role="alert" class="alert alert-<?= htmlspecialchars($alert['type']) ?> shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span><?= htmlspecialchars($alert['message']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Cartes de statistiques clés (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 animate-fade-in-up">
        <!-- Utilisateurs Actifs -->
        <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div class="flex flex-col">
                        <p class="text-sm text-base-content/70">Utilisateurs Actifs</p>
                        <p class="text-4xl font-extrabold text-primary"><?= htmlspecialchars($userStats['actif'] ?? 0) ?></p>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <progress class="progress progress-primary w-full" value="<?= $activeUsersPercent ?>" max="100"></progress>
                    <p class="text-xs text-base-content/60 mt-1"><?= $activeUsersPercent ?>% du total (<?= htmlspecialchars($totalUsers) ?>)</p>
                </div>
            </div>
        </div>

        <!-- Rapports Validés -->
        <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-base-content/70">Rapports Validés</p>
                        <p class="text-4xl font-extrabold text-success"><?= htmlspecialchars($reportStats['Validé'] ?? 0) ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <progress class="progress progress-success w-full" value="<?= $validatedReportsPercent ?>" max="100"></progress>
                    <p class="text-xs text-base-content/60 mt-1"><?= $validatedReportsPercent ?>% du total (<?= htmlspecialchars($totalReports) ?>)</p>
                </div>
            </div>
        </div>

        <!-- Tâches en échec -->
        <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-base-content/70">Tâches en Échec</p>
                        <p class="text-4xl font-extrabold text-error"><?= htmlspecialchars($queueStats['failed'] ?? 0) ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <progress class="progress progress-error w-full" value="<?= $failedQueuePercent ?>" max="100"></progress>
                    <p class="text-xs text-base-content/60 mt-1"><?= $failedQueuePercent ?>% du total (<?= htmlspecialchars($totalQueue) ?>)</p>
                </div>
            </div>
        </div>

        <!-- Réclamations Ouvertes -->
        <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-base-content/70">Réclamations Ouvertes</p>
                        <p class="text-4xl font-extrabold text-warning"><?= htmlspecialchars($claimStats['Ouverte'] ?? 0) ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-xs text-base-content/60 mt-1">À traiter en priorité</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liens rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 animate-fade-in-up" style="animation-delay: 0.2s;">
        <a href="/admin/utilisateurs" class="btn btn-lg h-auto py-4 flex-col">
            <span class="material-icons text-3xl mb-2">group</span> Gestion Utilisateurs
        </a>
        <a href="/admin/configuration" class="btn btn-lg h-auto py-4 flex-col">
            <span class="material-icons text-3xl mb-2">settings</span> Configuration
        </a>
        <a href="/admin/supervision" class="btn btn-lg h-auto py-4 flex-col">
            <span class="material-icons text-3xl mb-2">monitor</span> Supervision
        </a>
        <a href="/admin/reporting" class="btn btn-lg h-auto py-4 flex-col">
            <span class="material-icons text-3xl mb-2">analytics</span> Rapports & Stats
        </a>
    </div>

    <!-- Section des graphiques et détails -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Graphique d'activité récente -->
        <div class="card lg:col-span-2 bg-base-100 shadow-xl animate-fade-in-up" style="animation-delay: 0.4s;">
            <div class="card-body">
                <h2 class="card-title">Activité Récente (7 derniers jours)</h2>
                <div class="h-80">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition des statuts de rapports -->
        <div class="card bg-base-100 shadow-xl animate-fade-in-up" style="animation-delay: 0.6s;">
            <div class="card-body">
                <h2 class="card-title">Statuts des Rapports</h2>
                <div class="h-80 flex items-center justify-center">
                    <canvas id="reportsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Intégration de Chart.js pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Données PHP injectées dans le JavaScript
        const activityData = <?= json_encode($activityStats) ?>;
        const reportData = <?= json_encode($reportStats) ?>;

        // Graphique d'activité
        const activityCtx = document.getElementById('activityChart');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(activityData),
                    datasets: [{
                        label: 'Nombre d\'actions',
                        data: Object.values(activityData),
                        backgroundColor: 'rgba(26, 94, 99, 0.6)',
                        borderColor: 'rgba(26, 94, 99, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Graphique des rapports
        const reportsCtx = document.getElementById('reportsChart');
        if (reportsCtx) {
            new Chart(reportsCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(reportData),
                    datasets: [{
                        label: 'Rapports',
                        data: Object.values(reportData),
                        backgroundColor: [
                            '#1A5E63', // Soumis
                            '#FFC857', // En correction
                            '#28a745', // Validé
                            '#dc3545', // Refusé
                            '#6c757d', // Brouillon
                            '#17a2b8', // Conforme
                            '#ffc107'  // En commission
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
    });
</script>