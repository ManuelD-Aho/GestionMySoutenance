<?php
// src/Frontend/views/dashboards/teacher_dashboard_content.php
/**
 * @var string $userRole
 * @var array $currentUser (contains info of the current user)
 * @var array $stats (dashboard specific stats)
 * @var array $alerts
 * @var array $notifications
 * @var array $recent_activity
 */
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Tableau de Bord: <?php echo htmlspecialchars($userRole ?? 'Enseignant'); ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Vue d'ensemble</li>
    </ol>

    <div class="row">
        <!-- Example Content Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">Rapports à Évaluer</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white stretched-link" href="#"><?php echo $stats['reports_to_validate_count'] ?? 'N/A'; ?></span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <!-- Add more cards or content specific to teachers here -->
    </div>

    <p>Contenu spécifique au tableau de bord Enseignant.</p>
    <p>Bienvenue, <?php echo htmlspecialchars($currentUser['login_utilisateur'] ?? 'Utilisateur'); ?>!</p>
</div>
