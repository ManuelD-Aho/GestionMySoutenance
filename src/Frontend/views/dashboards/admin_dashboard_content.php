<?php
// File: src/Frontend/views/dashboards/admin_dashboard_content.php
// Description: Contenu spécifique pour le tableau de bord de l'Administrateur Système.
// Les variables comme $stats, $alerts, $recent_activity sont passées par DashboardController.
?>
<div class="space-y-6">
    <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Indicateurs Clés</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php
            $kpiCards = [
                ['title' => 'Utilisateurs Actifs', 'value' => $stats['active_users'] ?? 'N/A', 'details' => "Ét: {$stats['active_students']}, Ens: {$stats['active_teachers']}, Adm: {$stats['active_staff']}", 'color' => 'bg-blue-500'],
                ['title' => 'Rapports Déposés (Année)', 'value' => $stats['reports_submitted_year'] ?? 'N/A', 'color' => 'bg-green-500'],
                ['title' => 'Rapports en Attente Conformité', 'value' => $stats['reports_pending_conformity'] ?? 'N/A', 'color' => 'bg-yellow-500 text-yellow-800'],
                ['title' => 'Rapports en Commission', 'value' => $stats['reports_in_commission'] ?? 'N/A', 'color' => 'bg-indigo-500'],
                ['title' => 'Rapports Validés Commission', 'value' => $stats['reports_validated_commission'] ?? 'N/A', 'color' => 'bg-teal-500'],
                ['title' => 'Soutenances Planifiées', 'value' => $stats['defenses_planned'] ?? 'N/A', 'color' => 'bg-purple-500'],
                ['title' => 'Soutenances Réalisées (Année)', 'value' => $stats['defenses_done_year'] ?? 'N/A', 'color' => 'bg-pink-500'],
                ['title' => 'PV en Attente', 'value' => $stats['pvs_pending'] ?? 'N/A', 'color' => 'bg-red-500'],
            ];
            ?>
            <?php foreach ($kpiCards as $card): ?>
                <div class="<?= $card['color'] ?> text-white p-5 rounded-lg shadow-lg">
                    <h3 class="text-lg font-medium"><?= htmlspecialchars($card['title']) ?></h3>
                    <p class="text-3xl font-bold mt-1"><?= htmlspecialchars($card['value']) ?></p>
                    <?php if (isset($card['details'])): ?>
                        <p class="text-sm opacity-80 mt-1"><?= htmlspecialchars($card['details']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Visualisations</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-medium text-gray-700 mb-3">Évolution des Dépôts</h3>
                <div class="h-64 bg-gray-200 flex items-center justify-center rounded-md">
                    <p class="text-gray-500">[Placeholder pour graphique des dépôts]</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-medium text-gray-700 mb-3">Répartition des Statuts</h3>
                <div class="h-64 bg-gray-200 flex items-center justify-center rounded-md">
                    <p class="text-gray-500">[Placeholder pour graphique des statuts]</p>
                </div>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Alertes et Actions Requises</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <?php if (!empty($alerts)): ?>
                <ul class="space-y-3">
                    <?php foreach ($alerts as $alert): ?>
                        <li class="flex items-start p-3 rounded-md <?= ($alert['type'] ?? 'info') === 'warning' ? 'bg-yellow-50 border-l-4 border-yellow-400' : 'bg-blue-50 border-l-4 border-blue-400' ?>">
                            <div class="ml-3">
                                <p class="text-sm font-medium <?= ($alert['type'] ?? 'info') === 'warning' ? 'text-yellow-700' : 'text-blue-700' ?>">
                                    <?= htmlspecialchars($alert['message']) ?>
                                    <?php if (isset($alert['link'])): ?>
                                        <a href="<?= htmlspecialchars($alert['link']) ?>" class="font-semibold hover:underline ml-1">Voir</a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">Aucune alerte pour le moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Flux d'Activité Récent</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <?php if (!empty($recent_activity)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($recent_activity as $activity): ?>
                        <li class="py-3">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($activity['user']) ?></span>
                                <?= htmlspecialchars($activity['action']) ?>
                                <span class="text-xs text-gray-400 float-right"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($activity['timestamp']))) ?></span>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">Aucune activité récente à afficher.</p>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Raccourcis Rapides</h2>
        <div class="flex flex-wrap gap-3">
            <a href="/admin/users/create" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">Créer Utilisateur</a>
            <a href="/admin/settings" class="bg-accent hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">Paramètres Système</a>
            <a href="/admin/soutenance-process" class="bg-secondary hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">Gérer Soutenances</a>
        </div>
    </section>
</div>

