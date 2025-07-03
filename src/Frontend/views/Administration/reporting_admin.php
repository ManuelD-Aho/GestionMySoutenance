<?php
// src/Frontend/views/Administration/reporting_admin.php

// Fonction d'échappement HTML pour sécuriser l'affichage
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données pour les rapports proviendraient du contrôleur (ReportingController)
// Ces données sont des exemples pour structurer la vue.
//

// Exemple de données pour les statistiques générales
$stats_generales = [
    'total_connexions' => 15000,
    'utilisateurs_actifs_mois' => 950,
    'nouveaux_comptes_mois' => 120,
    'taux_engagement' => 75.2, // en %
];

// Exemple de données pour les rapports de soutenance (KPIs)
$kpis_soutenance = [
    'rapports_soumis' => 875,
    'rapports_en_attente_conformite' => 50,
    'rapports_en_evaluation_commission' => 30,
    'rapports_valides' => 700,
    'rapports_refuses' => 25,
    'taux_validation_global' => 80.0, // en %
    'delai_moyen_conformite_jours' => 3.5,
    'delai_moyen_evaluation_jours' => 7.1,
];

// Exemple de données pour un graphique (ex: Rapports soumis par mois)
$rapports_par_mois = [
    ['mois' => 'Jan', 'soumis' => 80, 'validés' => 70],
    ['mois' => 'Fév', 'soumis' => 90, 'validés' => 75],
    ['mois' => 'Mar', 'soumis' => 110, 'validés' => 95],
    ['mois' => 'Avr', 'soumis' => 100, 'validés' => 85],
    ['mois' => 'Mai', 'soumis' => 120, 'validés' => 105],
    ['mois' => 'Juin', 'soumis' => 95, 'validés' => 80],
];

// Exemple de données pour un tableau de suivi
$top_utilisateurs_actions = [
    ['id' => 'USR-001', 'profil' => 'Admin', 'actions' => 250, 'derniere_action' => '2025-06-29 10:30'],
    ['id' => 'USR-005', 'profil' => 'RS', 'actions' => 180, 'derniere_action' => '2025-06-29 14:15'],
    ['id' => 'USR-010', 'profil' => 'Agent Conformité', 'actions' => 150, 'derniere_action' => '2025-06-28 09:00'],
];

?>

<div class="reporting-container">
    <h1 class="reporting-title">Tableaux de Bord et Rapports Analytiques</h1>

    <section class="filters-section dashboard-card">
        <h2 class="section-title">Filtres de Rapports</h2>
        <form class="filter-form">
            <div class="form-group">
                <label for="periode-rapport">Période :</label>
                <select id="periode-rapport" name="periode">
                    <option value="current_month">Mois actuel</option>
                    <option value="last_3_months">3 derniers mois</option>
                    <option value="current_academic_year">Année académique en cours</option>
                    <option value="custom">Personnalisé</option>
                </select>
            </div>
            <div class="form-group" id="custom-date-range" style="display: none;">
                <label for="date_debut">Date de début :</label>
                <input type="date" id="date_debut" name="date_debut">
                <label for="date_fin">Date de fin :</label>
                <input type="date" id="date_fin" name="date_fin">
            </div>
            <div class="form-group">
                <label for="type-rapport">Type de rapport :</label>
                <select id="type-rapport" name="type_rapport">
                    <option value="all">Tous</option>
                    <option value="student_reports">Rapports Étudiants</option>
                    <option value="user_activity">Activité Utilisateurs</option>
                    <option value="system_health">Santé Système</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary-blue">Appliquer les filtres</button>
        </form>
    </section>

    <section class="reporting-section kpis-section">
        <h2 class="section-title">Indicateurs Clés de Performance (KPIs) - Soutenance</h2>
        <div class="stats-grid">
            <div class="dashboard-card stat-card">
                <h3>Rapports Soumis</h3>
                <p class="stat-value"><?= e($kpis_soutenance['rapports_soumis']); ?></p>
            </div>
            <div class="dashboard-card stat-card">
                <h3>Rapports Validés</h3>
                <p class="stat-value"><?= e($kpis_soutenance['rapports_valides']); ?></p>
                <p class="stat-change positive"><?= e($kpis_soutenance['taux_validation_global']); ?>% Validation</p>
            </div>
            <div class="dashboard-card stat-card alert-card">
                <h3>Rapports Refusés</h3>
                <p class="stat-value"><?= e($kpis_soutenance['rapports_refuses']); ?></p>
                <p class="stat-change negative"><?= e($kpis_soutenance['taux_refus']); ?>% Refus</p>
            </div>
            <div class="dashboard-card stat-card">
                <h3>Délai Conformité</h3>
                <p class="stat-value"><?= e($kpis_soutenance['delai_moyen_conformite_jours']); ?> jours</p>
            </div>
            <div class="dashboard-card stat-card">
                <h3>Délai Évaluation Commission</h3>
                <p class="stat-value"><?= e($kpis_soutenance['delai_moyen_evaluation_jours']); ?> jours</p>
            </div>
        </div>
    </section>

    <section class="reporting-section charts-section">
        <h2 class="section-title">Tendances des Rapports (Exemple de Graphique)</h2>
        <div class="dashboard-card chart-card">
            <canvas id="rapportsParMoisChart"></canvas>
            <p class="chart-description">Ce graphique montre le nombre de rapports soumis et validés par mois.</p>
        </div>
    </section>

    <section class="reporting-section detailed-data-section">
        <h2 class="section-title">Activité des Utilisateurs</h2>
        <div class="dashboard-card">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID Utilisateur</th>
                    <th>Profil</th>
                    <th>Nombre d'Actions</th>
                    <th>Dernière Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($top_utilisateurs_actions as $user_action): ?>
                    <tr>
                        <td><?= e($user_action['id']); ?></td>
                        <td><?= e($user_action['profil']); ?></td>
                        <td><?= e($user_action['actions']); ?></td>
                        <td><?= e($user_action['derniere_action']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logique pour le sélecteur de période personnalisé
        const periodeSelect = document.getElementById('periode-rapport');
        const customDateRange = document.getElementById('custom-date-range');

        if (periodeSelect) {
            periodeSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.style.display = 'flex'; // Utilisez flex pour un meilleur alignement des inputs
                } else {
                    customDateRange.style.display = 'none';
                }
            });
            // Assurer le bon affichage au chargement si l'option custom est déjà sélectionnée
            if (periodeSelect.value === 'custom') {
                customDateRange.style.display = 'flex';
            }
        }

    // --- Logique Chart.js pour le graphique des rapports par mois ---
    // Assurez-vous que Chart.js est inclus dans votre layout principal (app.php)
    // <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

const ctx = document.getElementById('rapportsParMoisChart');
if (ctx) {
const rapportsData = <?= json_encode($rapports_par_mois); ?>;
const labels = rapportsData.map(data => data.mois);
const soumisData = rapportsData.map(data => data.soumis);
const validesData = rapportsData.map(data => data.validés);

new Chart(ctx, {
type: 'line', // Type de graphique (line, bar, pie, etc.)
data: {
labels: labels,
datasets: [
{
label: 'Rapports Soumis',
data: soumisData,
borderColor: 'rgb(59, 130, 246)', // --primary-blue de root.css
backgroundColor: 'rgba(59, 130, 246, 0.2)',
tension: 0.3,
fill: false
},
{
label: 'Rapports Validés',
data: validesData,
borderColor: 'rgb(16, 185, 129)', // --primary-green de root.css
backgroundColor: 'rgba(16, 185, 129, 0.2)',
tension: 0.3,
fill: false
}
]
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
display: true,
position: 'top'
},
tooltip: {
mode: 'index',
intersect: false
}
}
}
});
}
});
</script>

<style>
    /* Styles spécifiques pour reporting_admin.php */
    .reporting-container {
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
    }

    .reporting-title {
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xl);
        text-align: center;
        font-weight: var(--font-weight-semibold);
    }

    .section-title {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-md);
        border-bottom: 1px solid var(--border-light);
        padding-bottom: var(--spacing-sm);
        font-weight: var(--font-weight-medium);
    }

    .filters-section {
        margin-bottom: var(--spacing-xl);
        padding: var(--spacing-lg);
        background-color: var(--bg-secondary); /* Couleur de fond légèrement différente */
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
    }

    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
        align-items: flex-end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group select,
    .form-group input[type="date"] {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
    }

    .form-group select:focus,
    .form-group input[type="date"]:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .filter-form button {
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-white);
        background-color: var(--primary-blue);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
    }

    .filter-form button:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    .kpis-section .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xl);
    }

    /* Réutilisation des styles des stat-card du dashboard_admin.php */
    .dashboard-card {
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-md);
    }

    .stat-card {
        text-align: center;
        border: 1px solid var(--border-light);
    }

    .stat-card h3 {
        font-size: var(--font-size-lg);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .stat-card .stat-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
    }

    .stat-card .stat-change {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
    }

    .stat-card .stat-change.positive { color: var(--success-color); }
    .stat-card .stat-change.negative { color: var(--error-color); }
    .stat-card .stat-change.neutral { color: var(--info-color); }

    .stat-card .stat-change .material-icons {
        font-size: var(--font-size-base);
        margin-right: var(--spacing-xs);
    }

    .charts-section {
        margin-bottom: var(--spacing-xl);
    }

    .chart-card {
        position: relative;
        height: 400px; /* Hauteur fixe pour le graphique */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .chart-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        text-align: center;
        margin-top: var(--spacing-md);
    }

    .detailed-data-section {
        margin-bottom: var(--spacing-xl);
    }

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
        background-color: var(--primary-gray-light); /* Couleur de fond pour les lignes paires */
    }

    .data-table tbody tr:hover {
        background-color: var(--border-medium); /* Survol des lignes */
        transition: background-color var(--transition-fast);
    }
</style>ok