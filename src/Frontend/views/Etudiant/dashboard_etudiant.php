<?php
// src/Frontend/views/Etudiant/dashboard_etudiant.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le tableau de bord de l'étudiant (proviennent du EtudiantDashboardController)
//

$student_data = $data['student_data'] ?? [
    'prenom' => 'Jean',
    'nom' => 'Dupont',
    'last_login' => '2025-06-30 10:00:00',
    'matricule' => 'ETU-2025-0001',
    'niveau_etude' => 'Master 2',
];

$report_status = $data['report_status'] ?? [
    'titre_rapport' => 'Optimisation des Processus Logistiques par IA',
    'current_stage' => 'En évaluation par la commission', // Soumis, En contrôle conformité, Non conforme, En évaluation par la commission, Approuvé, Approuvé sous réserve, Refusé
    'action_required' => false,
    'action_link' => '',
    'action_message' => '',
    'is_final_validation' => false, // Indique si le rapport est validé ou définitivement non validé
];

$pending_penalties = $data['pending_penalties'] ?? [
    'has_penalties' => true,
    'details' => ['montant' => 5000, 'type' => 'FINANCIERE'],
    'link_regularisation' => '/etudiant/penalites',
];

$notifications = $data['notifications'] ?? [
    ['id' => 1, 'message' => 'Votre rapport est maintenant en cours d\'évaluation par la commission.', 'type' => 'info', 'date' => '2025-06-30 11:00'],
    ['id' => 2, 'message' => 'Rappel : La date limite de soumission est le 31 juillet.', 'type' => 'warning', 'date' => '2025-06-25 09:00'],
    ['id' => 3, 'message' => 'Votre relevé de notes provisoire est disponible.', 'type' => 'success', 'date' => '2025-06-20 16:00'],
];

$quick_links_student = $data['quick_links_student'] ?? [
    ['label' => 'Soumettre mon Rapport', 'url' => '/etudiant/rapport/soumettre', 'icon' => 'upload_file'],
    ['label' => 'Suivi de mon Rapport', 'url' => '/etudiant/rapport/suivi', 'icon' => 'track_changes'],
    ['label' => 'Mes Documents Officiels', 'url' => '/etudiant/documents', 'icon' => 'folder_shared'],
    ['label' => 'Mon Profil', 'url' => '/etudiant/profile', 'icon' => 'account_circle'],
    ['label' => 'Mes Réclamations', 'url' => '/etudiant/reclamations/suivi', 'icon' => 'feedback'],
    ['label' => 'Ressources & Aide', 'url' => '/etudiant/ressources', 'icon' => 'help'],
];
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Bienvenue, <?= e($student_data['prenom']) . ' ' . e($student_data['nom']); ?> !</h1>
    <p class="dashboard-subtitle">Espace Étudiant - <?= e($student_data['niveau_etude']); ?></p>
    <p class="last-login-info">Dernière connexion : <?= e(date('d/m/Y H:i', strtotime($student_data['last_login']))); ?></p>

    <section class="overview-section admin-card">
        <h2 class="section-title">État de votre Rapport de Soutenance</h2>
        <?php if ($report_status['titre_rapport']): ?>
            <div class="report-status-card">
                <h3>Rapport : "<?= e($report_status['titre_rapport']); ?>"</h3>
                <p>Statut actuel :
                    <span class="status-indicator status-<?= strtolower(str_replace(' ', '-', e($report_status['current_stage']))); ?>">
                        <?= e($report_status['current_stage']); ?>
                    </span>
                </p>
                <?php if ($report_status['action_required']): ?>
                    <div class="alert alert-warning mt-md">
                        <span class="material-icons">assignment_late</span>
                        <p><?= e($report_status['action_message']); ?></p>
                        <?php if ($report_status['action_link']): ?>
                            <a href="<?= e($report_status['action_link']); ?>" class="btn-link ml-md">Agir maintenant</a>
                        <?php endif; ?>
                    </div>
                <?php elseif ($report_status['is_final_validation']): ?>
                    <div class="alert alert-success mt-md">
                        <span class="material-icons">check_circle</span>
                        Votre rapport a été finalisé !
                        <?php if ($report_status['action_link']): ?>
                            <a href="<?= e($report_status['action_link']); ?>" class="btn-link ml-md">Consulter le PV</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <a href="/etudiant/rapport/suivi" class="btn btn-secondary-gray btn-sm mt-md">
                    <span class="material-icons">visibility</span> Voir le suivi détaillé
                </a>
            </div>
        <?php else: ?>
            <div class="no-report-info text-center">
                <p class="text-muted">Vous n'avez pas encore soumis de rapport.</p>
                <a href="/etudiant/rapport/soumettre" class="btn btn-primary-blue mt-md">
                    <span class="material-icons">upload_file</span> Soumettre votre premier rapport
                </a>
            </div>
        <?php endif; ?>

        <?php if ($pending_penalties['has_penalties']): ?>
            <div class="alert alert-error mt-xl">
                <span class="material-icons">gpp_bad</span>
                <strong>Pénalités en attente !</strong>
                <p>Des pénalités (<?= e($pending_penalties['details']['type']); ?>: <?= e($pending_penalties['details']['montant']); ?> FCFA) sont appliquées à votre compte.</p>
                <p>Veuillez régulariser votre situation pour débloquer la soumission de rapport ou l'accès à certaines fonctionnalités.</p>
                <a href="<?= e($pending_penalties['link_regularisation']); ?>" class="btn-link ml-md">Régulariser ma situation</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-notifications-dashboard admin-card mt-xl">
        <h2 class="section-title">Notifications Récentes</h2>
        <?php if (!empty($notifications)): ?>
            <ul class="notifications-list">
                <?php foreach ($notifications as $notif): ?>
                    <li class="notification-item notification-<?= e($notif['type']); ?>">
                        <span class="material-icons icon-<?= e($notif['type']); ?>">
                            <?php
                            if ($notif['type'] === 'info') echo 'info';
                            elseif ($notif['type'] === 'success') echo 'check_circle';
                            elseif ($notif['type'] === 'warning') echo 'warning';
                            elseif ($notif['type'] === 'error') echo 'error';
                            else echo 'notifications';
                            ?>
                        </span>
                        <div class="notification-content">
                            <p class="notification-message"><?= e($notif['message']); ?></p>
                            <span class="notification-date"><?= e(date('d/m/Y H:i', strtotime($notif['date']))); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="text-center mt-lg">
                <a href="/dashboard/notifications" class="link-secondary">Voir toutes mes notifications</a>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune notification récente.</p>
        <?php endif; ?>
    </section>

    <section class="section-quick-links-student admin-card mt-xl">
        <h2 class="section-title">Accès Rapide</h2>
        <div class="quick-links-grid common-links-grid">
            <?php foreach ($quick_links_student as $link): ?>
                <a href="<?= e($link['url']); ?>" class="quick-action-btn">
                    <span class="material-icons"><?= e($link['icon']); ?></span>
                    <span><?= e($link['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript spécifique au tableau de bord étudiant si nécessaire.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour dashboard_etudiant.php */
    /* Réutilisation des classes de root.css et style.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre au dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1000px;
        margin: var(--spacing-xl) auto;
    }

    .dashboard-title { /* Réutilisé de dashboard.php */
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px solid var(--border-light);
    }

    .dashboard-subtitle, .last-login-info { /* Réutilisé de dashboard.php */
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        text-align: center;
        margin-bottom: var(--spacing-sm);
    }
    .last-login-info {
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-xl);
    }

    .admin-card { /* Réutilisé des modules d'administration */
        background-color: var(--bg-secondary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .section-title { /* Réutilisé des formulaires admin */
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        font-weight: var(--font-weight-medium);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    /* État du rapport de soutenance */
    .report-status-card {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-sm);
    }

    .report-status-card h3 {
        font-size: var(--font-size-lg);
        color: var(--primary-blue-dark);
        margin-bottom: var(--spacing-sm);
        font-weight: var(--font-weight-semibold);
    }

    .report-status-card p {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
    }

    /* Statut du rapport (réutilisé de suivi_rapport.php) */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-soumis, .status-en-contrôle-conformité, .status-en-évaluation-par-la-commission {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    .status-non-conforme, .status-refusé {
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
    }

    .status-approuvé, .status-validé {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
    }

    .status-approuvé-sous-réserve {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    /* Alertes (réutilisées) */
    .alert {
        padding: var(--spacing-md);
        border-radius: var(--border-radius-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-md);
        margin-top: var(--spacing-md); /* Pour être séparé des éléments au-dessus */
        text-align: left;
        border: 1px solid;
        background-color: var(--bg-primary);
    }

    .alert-warning {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
        border-color: var(--accent-yellow-dark);
    }

    .alert-success {
        background-color: var(--primary-green-light);
        color: var(--primary-green-dark);
        border-color: var(--primary-green-dark);
    }

    .alert-error { /* Pour les pénalités */
        background-color: var(--accent-red-light);
        color: var(--accent-red-dark);
        border-color: var(--accent-red-dark);
    }
    .alert-error strong.text-red { /* Pour le texte de pénalité */
        color: var(--accent-red-dark);
    }

    .alert .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .btn-link { /* Réutilisé de suivi_rapport.php */
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: var(--font-weight-semibold);
        margin-left: var(--spacing-md);
        transition: color var(--transition-fast), text-decoration var(--transition-fast);
    }
    .btn-link:hover {
        color: var(--primary-blue-dark);
        text-decoration: underline;
    }

    .btn-sm { /* Bouton "Voir le suivi détaillé" */
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: var(--font-size-sm);
    }

    .btn-primary-blue { /* Pour soumettre le rapport si pas encore fait */
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-secondary-gray {
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }

    /* Section "Vous n'avez pas encore soumis de rapport" */
    .no-report-info {
        padding: var(--spacing-lg);
        background-color: var(--primary-white);
        border-radius: var(--border-radius-md);
        border: 1px solid var(--border-light);
        margin-top: var(--spacing-md);
    }

    /* Notifications sur le dashboard (réutilisé du common/notifications_panel.php) */
    .notifications-list {
        list-style: none;
        padding: 0;
    }

    .notification-item {
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-sm) var(--spacing-md);
        margin-bottom: var(--spacing-sm);
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-sm);
        box-shadow: var(--shadow-sm);
    }

    .notification-item .material-icons {
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }
    .notification-item .icon-info { color: var(--info-color); }
    .notification-item .icon-success { color: var(--success-color); }
    .notification-item .icon-warning { color: var(--warning-color); }
    .notification-item .icon-error { color: var(--error-color); }

    .notification-content { flex-grow: 1; }
    .notification-message {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
    }
    .notification-date {
        font-size: var(--font-size-sm);
        color: var(--text-light);
    }

    /* Grille de liens rapides (réutilisé du common/dashboard.php) */
    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-lg);
        background-color: var(--primary-white);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        text-decoration: none;
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        transition: all var(--transition-fast);
        cursor: pointer;
        min-height: 120px;
    }

    .quick-action-btn:hover {
        background-color: var(--primary-blue-light);
        color: var(--text-white);
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .quick-action-btn:hover .material-icons {
        color: var(--text-white);
    }

    .quick-action-btn .material-icons {
        font-size: var(--font-size-4xl);
        color: var(--primary-blue);
        margin-bottom: var(--spacing-sm);
        transition: color var(--transition-fast);
    }

    .quick-action-btn span:last-child {
        text-align: center;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
</style>