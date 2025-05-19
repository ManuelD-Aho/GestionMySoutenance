<?php
// File: src/Frontend/views/dashboards/student_dashboard_content.php
// Description: Contenu spécifique pour le tableau de bord de l'Étudiant.
// Les variables comme $my_report_status, $next_deadline, $notifications sont passées par DashboardController.
?>
<div class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Mon Rapport Actuel</h2>
        <p class="text-gray-600">Statut: <span class="font-medium text-primary"><?= htmlspecialchars($my_report_status ?? 'N/A') ?></span></p>
        <p class="text-gray-600 mt-1">Prochaine échéance: <span class="font-medium"><?= htmlspecialchars($next_deadline ?? 'N/A') ?></span></p>
        <div class="mt-4">
            <a href="/student/my-reports" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">Voir mes rapports</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Notifications</h2>
        <?php if (!empty($notifications)): ?>
            <ul class="space-y-2">
                <?php foreach ($notifications as $notif): ?>
                    <li class="text-sm text-gray-600 p-2 bg-blue-50 rounded-md">
                        <span class="font-medium"><?= htmlspecialchars($notif['date']) ?>:</span> <?= htmlspecialchars($notif['message']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">Aucune nouvelle notification.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// File: src/Frontend/views/dashboards/teacher_dashboard_content.php
// Description: Contenu spécifique pour le tableau de bord de l'Enseignant.
// Les variables comme $reports_to_validate_count, $supervision_count, $upcoming_defenses_jury sont passées.
?>
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-700">Rapports à Évaluer/Valider</h3>
            <p class="text-3xl font-bold text-primary mt-1"><?= htmlspecialchars($reports_to_validate_count ?? 0) ?></p>
            <a href="/teacher/reports-to-evaluate" class="text-sm text-primary hover:underline mt-2 inline-block">Voir la liste</a>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-700">Encadrements en Cours</h3>
            <p class="text-3xl font-bold text-secondary mt-1"><?= htmlspecialchars($supervision_count ?? 0) ?></p>
            <a href="/teacher/my-supervisions" class="text-sm text-secondary hover:underline mt-2 inline-block">Gérer mes encadrements</a>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-700">Participations Jury à Venir</h3>
            <p class="text-3xl font-bold text-accent mt-1"><?= htmlspecialchars($upcoming_defenses_jury ?? 0) ?></p>
            <a href="/teacher/my-jury-schedule" class="text-sm text-accent hover:underline mt-2 inline-block">Consulter mon agenda</a>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Actions rapides</h2>
        <div class="flex flex-wrap gap-3">
            <a href="/teacher/reports-to-evaluate" class="bg-primary hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">Évaluer Rapports</a>
            <a href="/teacher/my-supervisions" class="bg-secondary hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">Mes Encadrements</a>
        </div>
    </div>
</div>

<?php
// File: src/Frontend/views/dashboards/default_dashboard_content.php
// Description: Contenu par défaut si aucun rôle spécifique n'est trouvé ou pour les rôles sans dashboard personnalisé.
?>
<div class="bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-semibold text-gray-700">Bienvenue sur Gestion MySoutenance</h1>
    <p class="text-gray-600 mt-2">Votre tableau de bord personnalisé est en cours de préparation.</p>
    <p class="text-gray-600 mt-1">Utilisez le menu sur la gauche pour naviguer dans l'application.</p>
</div>

