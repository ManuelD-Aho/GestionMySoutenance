<?php
// src/Frontend/views/Commission/dashboard_commission.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour le tableau de bord (proviennent du contrôleur CommissionDashboardController)
//
//

$commission_dashboard_data = $data['commission_dashboard_data'] ?? [
    'rapports_a_evaluer' => [
        ['id' => 1, 'numero' => 'RAP-2025-0045', 'titre' => 'Optimisation Logistique par IA', 'etudiant_nom' => 'Dupont Jean', 'date_soumission' => '2025-06-28', 'statut' => 'En attente d\'évaluation'],
        ['id' => 2, 'numero' => 'RAP-2025-0046', 'titre' => 'Analyse de Données Financières', 'etudiant_nom' => 'Curie Marie', 'date_soumission' => '2025-06-29', 'statut' => 'En attente d\'évaluation'],
    ],
    'pvs_a_approuver' => [
        ['id' => 10, 'numero' => 'PV-2025-0010', 'titre_rapport' => 'Rapport sur Systèmes Embarqués', 'etudiant_nom' => 'Voltaire François', 'date_redaction' => '2025-06-30'],
    ],
    'sessions_actives_ou_prochaines' => [
        ['id' => 100, 'libelle' => 'Session Juin 2025 - Vague 1', 'date_debut' => '2025-06-25', 'date_fin' => '2025-07-05', 'type' => 'Hybride', 'statut' => 'En cours'],
        ['id' => 101, 'libelle' => 'Session Juillet 2025 - Rattrapages', 'date_debut' => '2025-07-10', 'date_fin' => '2025-07-15', 'type' => 'En Ligne (Asynchrone)', 'statut' => 'Planifiée'],
    ],
    'notifications_recues' => [
        ['id' => 1, 'message' => 'Nouveau rapport "IA en Logistique" à évaluer.', 'type' => 'info', 'date' => '2025-06-28 10:00'],
        ['id' => 2, 'message' => 'PV "Systèmes Embarqués" soumis pour votre approbation.', 'type' => 'warning', 'date' => '2025-06-30 09:30'],
    ]
];

?>

<div class="admin-module-container">
    <h1 class="admin-title">Tableau de Bord Commission</h1>

    <section class="overview-section admin-card">
        <h2 class="section-title">Vos Tâches en Attente</h2>
        <div class="stats-grid dashboard-commission-stats">
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Rapports à Évaluer</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">assignment_turned_in</span></div>
                </div>
                <p class="stat-value"><?= e(count($commission_dashboard_data['rapports_a_evaluer'])); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">PV à Approuver</h3>
                    <div class="stat-icon icon-bg-yellow"><span class="material-icons">description</span></div>
                </div>
                <p class="stat-value"><?= e(count($commission_dashboard_data['pvs_a_approuver'])); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Sessions Actives/Prochaines</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">event_note</span></div>
                </div>
                <p class="stat-value"><?= e(count($commission_dashboard_data['sessions_actives_ou_prochaines'])); ?></p>
            </div>
        </div>
    </section>

    <section class="section-rapports-a-traiter admin-card mt-xl">
        <div class="section-header">
            <h2 class="section-title">Rapports En Attente d'Évaluation</h2>
            <a href="/commission/rapports/liste" class="btn btn-secondary-gray">
                <span class="material-icons">list_alt</span> Voir tous
            </a>
        </div>
        <?php if (!empty($commission_dashboard_data['rapports_a_evaluer'])): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Numéro Rapport</th>
                    <th>Titre</th>
                    <th>Étudiant</th>
                    <th>Date Soumission</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($commission_dashboard_data['rapports_a_evaluer'] as $rapport): ?>
                    <tr>
                        <td><?= e($rapport['numero']); ?></td>
                        <td><?= e(mb_strimwidth($rapport['titre'], 0, 50, '...')); ?></td>
                        <td><?= e($rapport['etudiant_nom']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($rapport['date_soumission']))); ?></td>
                        <td class="actions">
                            <a href="/commission/rapports/details/<?= e($rapport['id']); ?>" class="btn-action view-btn" title="Consulter le rapport">
                                <span class="material-icons">visibility</span>
                            </a>
                            <a href="/commission/rapports/vote/<?= e($rapport['id']); ?>" class="btn-action evaluate-btn" title="Évaluer / Voter">
                                <span class="material-icons">how_to_vote</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Aucun rapport en attente d'évaluation.</p>
        <?php endif; ?>
    </section>

    <section class="section-pvs-a-approuver admin-card mt-xl">
        <div class="section-header">
            <h2 class="section-title">Procès-Verbaux à Approuver</h2>
            <a href="/commission/pv/liste-a-valider" class="btn btn-secondary-gray">
                <span class="material-icons">playlist_add_check</span> Voir tous
            </a>
        </div>
        <?php if (!empty($commission_dashboard_data['pvs_a_approuver'])): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Numéro PV</th>
                    <th>Rapport</th>
                    <th>Étudiant</th>
                    <th>Date Rédaction</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($commission_dashboard_data['pvs_a_approuver'] as $pv): ?>
                    <tr>
                        <td><?= e($pv['numero']); ?></td>
                        <td><?= e(mb_strimwidth($pv['titre_rapport'], 0, 50, '...')); ?></td>
                        <td><?= e($pv['etudiant_nom']); ?></td>
                        <td><?= e(date('d/m/Y', strtotime($pv['date_redaction']))); ?></td>
                        <td class="actions">
                            <a href="/commission/pv/consulter/<?= e($pv['id']); ?>" class="btn-action view-btn" title="Consulter le PV">
                                <span class="material-icons">visibility</span>
                            </a>
                            <a href="/commission/pv/valider/<?= e($pv['id']); ?>" class="btn-action approve-btn" title="Approuver le PV">
                                <span class="material-icons">check_circle</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Aucun PV en attente d'approbation.</p>
        <?php endif; ?>
    </section>

    <section class="section-sessions admin-card mt-xl">
        <h2 class="section-title">Vos Sessions de Validation</h2>
        <?php if (!empty($commission_dashboard_data['sessions_actives_ou_prochaines'])): ?>
            <div class="sessions-list-grid">
                <?php foreach ($commission_dashboard_data['sessions_actives_ou_prochaines'] as $session): ?>
                    <div class="session-card session-status-<?= e(strtolower(str_replace(' ', '-', $session['statut']))); ?>">
                        <div class="card-header">
                            <h3 class="card-title"><?= e($session['libelle']); ?></h3>
                            <span class="status-indicator status-<?= e(strtolower(str_replace(' ', '-', $session['statut']))); ?>"><?= e($session['statut']); ?></span>
                        </div>
                        <div class="card-body">
                            <p><strong>Période :</strong> <?= e(date('d/m/Y', strtotime($session['date_debut']))) ?> - <?= e(date('d/m/Y', strtotime($session['date_fin']))) ?></p>
                            <p><strong>Type :</strong> <?= e($session['type']); ?></p>
                            <a href="/commission/sessions/details/<?= e($session['id']); ?>" class="link-secondary mt-md">Voir les détails</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune session active ou prochaine.</p>
        <?php endif; ?>
    </section>

    <section class="section-notifications admin-card mt-xl">
        <h2 class="section-title">Notifications Récentes</h2>
        <?php if (!empty($commission_dashboard_data['notifications_recues'])): ?>
            <ul class="notifications-list">
                <?php foreach ($commission_dashboard_data['notifications_recues'] as $notif): ?>
                    <li class="notification-item notification-<?= e($notif['type']); ?>">
                        <span class="material-icons icon-<?= e($notif['type']); ?>"><?= $notif['type'] === 'info' ? 'info' : ($notif['type'] === 'warning' ? 'warning' : 'notifications'); ?></span>
                        <div class="notification-content">
                            <p class="notification-message"><?= e($notif['message']); ?></p>
                            <span class="notification-date"><?= e(date('d/m/Y H:i', strtotime($notif['date']))); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center text-muted">Aucune notification récente.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique JavaScript spécifique au tableau de bord de la commission si nécessaire.
        // Par exemple, pour des mises à jour en temps réel des compteurs ou des listes.

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour dashboard_commission.php */
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

    /* Grille de statistiques (réutilisation des styles du dashboard admin) */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .dashboard-card {
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
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-2xl);
        color: var(--text-white);
    }

    /* Couleurs des icônes de statistiques (réutilisées de root.css) */
    .icon-bg-red { background-color: var(--accent-red-light); }
    .icon-bg-yellow { background-color: var(--accent-yellow-light); }
    .icon-bg-blue { background-color: var(--primary-blue-light); }
    .icon-bg-green { background-color: var(--primary-green-light); }

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
    .dashboard-card .stat-change.negative { color: var(--accent-red); }
    .dashboard-card .stat-change.neutral { color: var(--text-secondary); }
    .dashboard-card .stat-change.positive { color: var(--primary-green); }


    /* Sections de liste de rapports et PV */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

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

    .btn-secondary-gray {
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }

    .btn-secondary-gray:hover {
        background-color: var(--border-medium);
        box-shadow: var(--shadow-sm);
    }


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

    .btn-action.view-btn { color: var(--primary-blue); }
    .btn-action.view-btn:hover { background-color: rgba(59, 130, 246, 0.1); }

    .btn-action.evaluate-btn, .btn-action.approve-btn { color: var(--primary-green); }
    .btn-action.evaluate-btn:hover, .btn-action.approve-btn:hover { background-color: rgba(16, 185, 129, 0.1); }


    /* Statuts des rapports/PV */
    .status-indicator {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-en-attente-d-évaluation, .status-en-attente, .status-planifiée {
        background-color: var(--accent-yellow-light);
        color: var(--accent-yellow-dark);
    }

    .status-en-cours, .status-en-cours-de-vote {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue-dark);
    }

    /* Grille et cartes des sessions */
    .sessions-list-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-md);
    }

    .session-card {
        background-color: var(--primary-white);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .session-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-sm);
        border-bottom: 1px solid var(--border-light);
        padding-bottom: var(--spacing-xs);
    }

    .session-card .card-title {
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        margin: 0;
        font-weight: var(--font-weight-semibold);
    }

    .session-card .card-body p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
    }

    .session-card .card-body strong {
        color: var(--text-primary);
    }

    .session-card .link-secondary {
        align-self: flex-end; /* Aligne le lien en bas à droite de la carte */
    }

    /* Notifications récentes */
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
    .notification-item .icon-warning { color: var(--warning-color); }
    .notification-item .icon-notifications { color: var(--text-secondary); } /* Icône générique */

    .notification-content {
        flex-grow: 1;
    }

    .notification-message {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
    }

    .notification-date {
        font-size: var(--font-size-sm);
        color: var(--text-light);
    }

    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }
</style>