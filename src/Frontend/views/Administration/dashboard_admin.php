<?php
// src/Frontend/views/Administration/dashboard_admin.php

// Fonction d'échappement HTML pour sécuriser l'affichage
// Si elle n'est pas déjà définie par le layout principal ou un utilitaire global
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les variables suivantes ($stats, $alertes, $liens_rapides) devraient être passées
// au template via le contrôleur (AdminDashboardController) dans un scénario réel.
// Pour la démonstration de la vue, nous les initialisons ici avec des données fictives.

// Exemple de données de statistiques (proviennent de ServiceReportingAdmin et ServiceSupervisionAdmin)
$statistiques_rapports = $dashboard_specific_data['statistiques_rapports'] ?? [
    'total_rapports' => 0,
    'rapports_valides' => 0,
    'rapports_refuses' => 0,
    'rapports_en_attente' => 0,
    'taux_validation' => 0,
    'taux_refus' => 0,
]; //

$statistiques_utilisation = $dashboard_specific_data['statistiques_utilisation'] ?? [
    'total_utilisateurs' => 0,
    'utilisateurs_par_type' => [],
    'total_actions_journalisees' => 0,
    'connexions_recentes_7j' => 0,
]; //

$global_rapports_stats = $dashboard_specific_data['global_rapports_stats'] ?? [
    'total_rapports_soumis' => 0,
    'rapports_en_attente_conformite' => 0,
    'rapports_en_attente_commission' => 0,
    'rapports_finalises' => 0,
]; //

// Simuler les alertes système
$alertes_systeme = [
    ['type' => 'danger', 'message' => 'Base de données : Connexion impossible.', 'source' => 'DB_CONNEXION'],
    ['type' => 'warning', 'message' => 'Serveur Web : Utilisation CPU élevée (92%).', 'source' => 'SERVER_CPU'],
    ['type' => 'info', 'message' => 'Nouvelle version de sécurité disponible.', 'source' => 'UPDATE_SECURITE'],
]; // Ces alertes sont mentionnées dans PARCOURS COMPLET DE L’ADMINISTRACTEUR SYSTEME.docx

// Liens directs vers les sections de gestion les plus critiques
$liens_rapides_gestion = [
    ['label' => 'Gestion des Utilisateurs', 'url' => '/dashboard/admin/utilisateurs', 'icon' => 'group'], //
    ['label' => 'Configuration Système', 'url' => '/dashboard/admin/config', 'icon' => 'settings_applications'], //
    ['label' => 'Journaux d\'Audit', 'url' => '/dashboard/admin/supervision/journaux-audit', 'icon' => 'history_toggle_off'], //
    ['label' => 'Modèles & Documents', 'url' => '/dashboard/admin/config/templates', 'icon' => 'description'], //
];

// Exemple de données pour les activités récentes (proviennent de ServiceSupervisionAdmin)
$last_audit_logs = $dashboard_specific_data['last_audit_logs'] ?? [
    ['numero_utilisateur' => 'SYS-2025-0001', 'libelle_action_ref' => 'CONNEXION_REUSSIE', 'date_action' => date('Y-m-d H:i:s', strtotime('-5 minutes')), 'type_entite_concernee' => 'Utilisateur', 'id_entite_concernee' => 'SYS-2025-0001'],
    ['numero_utilisateur' => 'ADM-2025-0001', 'libelle_action_ref' => 'CREATION_COMPTE', 'date_action' => date('Y-m-d H:i:s', strtotime('-30 minutes')), 'type_entite_concernee' => 'Etudiant', 'id_entite_concernee' => 'ETU-2025-0002'],
    ['numero_utilisateur' => 'ADM-2025-0002', 'libelle_action_ref' => 'VERIF_CONFORMITE_RAPPORT', 'date_action' => date('Y-m-d H:i:s', strtotime('-1 heure')), 'type_entite_concernee' => 'RapportEtudiant', 'id_entite_concernee' => 'RAP-2025-0010'],
    ['numero_utilisateur' => 'SYS-2025-0001', 'libelle_action_ref' => 'MODIF_GROUPE_UTILISATEUR', 'date_action' => date('Y-m-d H:i:s', strtotime('-2 heures')), 'type_entite_concernee' => 'GroupeUtilisateur', 'id_entite_concernee' => 'GRP_ETUDIANT'],
]; //

// Fonction utilitaire pour le formatage du temps (à intégrer dans un fichier JS global ou ici)
function timeAgo($dateString) {
    if (!$dateString) return 'N/A';
    $date = new DateTime($dateString);
    $now = new DateTime();
    $interval = $now->diff($date);

    if ($interval->y > 0) return 'il y a ' . $interval->y . ' an(s)';
    if ($interval->m > 0) return 'il y a ' . $interval->m . ' mois';
    if ($interval->d > 0) return 'il y a ' . $interval->d . ' jour(s)';
    if ($interval->h > 0) return 'il y a ' . $interval->h . ' heure(s)';
    if ($interval->i > 0) return 'il y a ' . $interval->i . ' minute(s)';
    return 'il y a quelques secondes';
}

// Mappage simplifié des actions pour les icônes et couleurs dans les logs
function getLogIconAndColor($actionCode) {
    $icon = 'info';
    $colorClass = 'icon-bg-blue'; // Default info color
    switch ($actionCode) {
        case 'CONNEXION_REUSSIE':
        case 'CREATION_COMPTE':
            $icon = 'person_add';
            $colorClass = 'icon-bg-green'; //
            break;
        case 'VERIF_CONFORMITE_RAPPORT':
            $icon = 'check_circle';
            $colorClass = 'icon-bg-orange'; //
            break;
        case 'MODIF_GROUPE_UTILISATEUR':
            $icon = 'edit';
            $colorClass = 'icon-bg-blue'; //
            break;
        case 'ECHEC_LOGIN':
        case 'COMPTE_BLOQUE':
            $icon = 'error';
            $colorClass = 'icon-bg-red'; //
            break;
        // Ajoutez d'autres cas pour vos actions
    }
    return ['icon' => $icon, 'colorClass' => $colorClass];
}

// Obtenir le libellé du type d'utilisateur (si disponible)
function getUserTypeLabel($typeId, $utilisateursParType) {
    // Dans une vraie implémentation, on ferait un appel à un service de configuration
    // pour récupérer le libellé de tous les types d'utilisateurs.
    // Ici, nous nous basons sur la simulation $statistiques_utilisation
    if (isset($utilisateursParType[$typeId])) {
        // Cette logique doit être plus robuste, idéalement un tableau de mapping
        // entre id_type_utilisateur et libelle_type_utilisateur.
        // Pour l'exemple, nous retournons l'ID lui-même.
        return str_replace('TYPE_', '', $typeId);
    }
    return $typeId;
}

?>

<div id="dashboard-content" class="content-section active">
    <section class="overview-section">
        <div class="section-header">
            <h2 class="section-title">Vue d'Ensemble Système</h2>
            <div class="time-range">
                <select id="time-range-selector" onchange="updateDashboardStats()">
                    <option value="today">Aujourd'hui</option>
                    <option value="week" selected>7 derniers jours</option>
                    <option value="month">30 derniers jours</option>
                    <option value="year">Cette année</option>
                </select>
            </div>
        </div>
        <div class="stats-grid" id="dashboard-stats-grid">
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Rapports Soumis</h3>
                    <div class="stat-icon icon-bg-orange"><span class="material-icons">assignment</span></div>
                </div>
                <p class="stat-value"><?= e($global_rapports_stats['total_rapports_soumis']); ?></p>
                <p class="stat-change neutral"><span class="material-icons">timeline</span>Total</p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Rapports Validés</h3>
                    <div class="stat-icon icon-bg-green"><span class="material-icons">check_circle</span></div>
                </div>
                <p class="stat-value"><?= e($statistiques_rapports['rapports_valides']); ?></p>
                <p class="stat-change positive"><span class="material-icons">trending_up</span><?= e($statistiques_rapports['taux_validation']); ?>%</p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <div class="stat-header">
                    <h3 class="stat-label">Rapports Non Conformes / Refusés</h3>
                    <div class="stat-icon icon-bg-red"><span class="material-icons">cancel</span></div>
                </div>
                <p class="stat-value"><?= e($statistiques_rapports['rapports_refuses']); ?></p>
                <p class="stat-change negative"><span class="material-icons">trending_down</span><?= e($statistiques_rapports['taux_refus']); ?>%</p>
            </div>
            <div class="dashboard-card stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Rapports en Attente</h3>
                    <div class="stat-icon icon-bg-blue"><span class="material-icons">pending_actions</span></div>
                </div>
                <p class="stat-value"><?= e($global_rapports_stats['rapports_en_attente_conformite'] + $global_rapports_stats['rapports_en_attente_commission']); ?></p>
                <p class="stat-change neutral"><span class="material-icons">hourglass_empty</span>Conformité / Commission</p>
            </div>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="dashboard-card quick-actions-card">
            <div class="card-header">
                <h3 class="card-title">Actions Rapides - Utilisateurs</h3>
                <button class="card-action" onclick="navigateToSection('users')">
                    <span class="material-icons">open_in_new</span>
                </button>
            </div>
            <div class="quick-actions-grid">
                <?php foreach ($liens_rapides_gestion as $lien): // Réutilisons les liens définis plus haut ?>
                    <button class="quick-action-btn" onclick="window.location.href='<?= e($lien['url']); ?>'">
                        <span class="material-icons"><?= e($lien['icon']); ?></span>
                        <span><?= e($lien['label']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="user-type-summary" id="dashboard-user-type-summary">
                <div class="summary-item">
                    <span class="summary-label">Utilisateurs Enregistrés</span>
                    <span class="summary-count"><?= e($statistiques_utilisation['total_utilisateurs']); ?></span>
                </div>
                <?php foreach ($statistiques_utilisation['utilisateurs_par_type'] as $type => $count): ?>
                    <div class="summary-item">
                        <span class="summary-label"><?= e(ucfirst(getUserTypeLabel($type, $statistiques_utilisation['utilisateurs_par_type']))); ?>s</span>
                        <span class="summary-count"><?= e($count); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dashboard-card activities-card">
            <div class="card-header">
                <h3 class="card-title">Activités Récentes du Système</h3>
                <div class="activity-filters" id="dashboard-activity-filters">
                    <button class="filter-btn active" data-filter="all">Toutes</button>
                    <button class="filter-btn" data-filter="user_management">Utilisateurs</button>
                    <button class="filter-btn" data-filter="security">Sécurité</button>
                    <button class="filter-btn" data-filter="system_config">Configuration</button>
                </div>
            </div>
            <div class="activities-list" id="dashboard-activities-list">
                <?php if (!empty($last_audit_logs)): ?>
                    <?php foreach ($last_audit_logs as $log):
                        $iconInfo = getLogIconAndColor($log['libelle_action_ref']);
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= e($iconInfo['colorClass']); ?>">
                                <span class="material-icons"><?= e($iconInfo['icon']); ?></span>
                            </div>
                            <div class="activity-details">
                                <p class="activity-text">
                                    <?= e($log['libelle_action_ref']); ?> par <strong><?= e($log['numero_utilisateur']); ?></strong>
                                    <?php if (!empty($log['type_entite_concernee'])): ?>
                                        sur <?= e($log['type_entite_concernee']); ?> (<?= e($log['id_entite_concernee']); ?>)
                                    <?php endif; ?>
                                </p>
                                <p class="activity-time"><?= timeAgo($log['date_action']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Aucune activité récente à afficher.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card system-health-card">
            <div class="card-header">
                <h3 class="card-title">État du Système</h3>
                <div class="health-status" id="dashboard-overall-health">
                    <span class="status-indicator status-healthy"></span>
                    <span>Opérationnel</span>
                </div>
            </div>
            <div class="health-metrics" id="dashboard-health-metrics">
                <div class="metric-item">
                    <div class="metric-header">
                        <span class="metric-label">Base de Données</span>
                        <span class="metric-status healthy">OK</span>
                    </div>
                    <div class="metric-bar"><div class="metric-fill" style="width: 80%"></div></div>
                    <span class="metric-value">80% utilisé</span>
                </div>
                <div class="metric-item">
                    <div class="metric-header">
                        <span class="metric-label">Serveur Web</span>
                        <span class="metric-status warning">Charge</span>
                    </div>
                    <div class="metric-bar"><div class="metric-fill warning" style="width: 75%"></div></div>
                    <span class="metric-value">75% charge CPU</span>
                </div>
                <div class="metric-item">
                    <div class="metric-header">
                        <span class="metric-label">Stockage Principal</span>
                        <span class="metric-status error">Critique</span>
                    </div>
                    <div class="metric-bar"><div class="metric-fill error" style="width: 95%"></div></div>
                    <span class="metric-value">95% utilisé</span>
                </div>
            </div>
        </div>

        <div class="dashboard-card permissions-overview-card">
            <div class="card-header">
                <h3 class="card-title">Aperçu des Alertes</h3>
                <button class="card-action" onclick="navigateToSection('audit_logs')">
                    <span class="material-icons">visibility</span>
                </button>
            </div>
            <div class="permissions-summary" id="dashboard-permissions-summary">
                <?php if (!empty($alertes_systeme)): ?>
                    <?php foreach ($alertes_systeme as $alerte): ?>
                        <div class="alert alert-<?= e($alerte['type']); ?>">
                            <span class="material-icons">
                                <?php
                                if ($alerte['type'] === 'danger') echo 'error';
                                elseif ($alerte['type'] === 'warning') echo 'warning';
                                else echo 'info';
                                ?>
                            </span>
                            <div>
                                <strong><?= e($alerte['message']); ?></strong>
                                <small>(Source: <?= e($alerte['source']); ?>)</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Aucune alerte système active.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
    // Vous pouvez placer des scripts spécifiques à cette vue ici,
    // ou si vous avez un fichier JS global pour le dashboard,
    // assurez-vous que les fonctions comme `updateDashboardStats` ou `MapsToSection` y sont définies.
    // Pour l'exemple, `gestionsoutenance-dashboard.js` est censé contenir ces logiques.

    // Cette fonction pourrait être appelée par `gestionsoutenance-dashboard.js`
    // pour mettre à jour le contenu spécifique de cette section.
    function initAdminDashboard() {
        // Logique pour charger des graphiques, interagir avec des APIs pour les données réelles, etc.
        console.log("Tableau de bord Admin initialisé.");

        // Exemple: Charger les notifications dans le header si ce n'est pas déjà fait
        // (La logique du header est dans header.php et gestionsoutenance-dashboard.js)
        // Si vous avez un mécanisme de flash messages, assurez-vous qu'ils s'affichent ici.
    }

    // Appel à l'initialisation si cette vue est chargée directement ou par AJAX
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminDashboard);
    } else {
        initAdminDashboard();
    }
</script>