<?php
/**
 * Gestion Académique - Page d'accueil modernisée
 * Fichier: src/Frontend/views/Administration/GestionAcad/index.php
 */

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données statistiques (normalement depuis le contrôleur)
$stats_gestion_acad = $data['stats_gestion_acad'] ?? [
    'total_etudiants_inscrits' => 1250,
    'total_ues' => 50,
    'total_ecues' => 180,
    'stages_en_cours' => 320,
    'inscriptions_en_attente_paiement' => 15,
    'nouveaux_inscrits_semaine' => 23,
    'taux_reussite_global' => 87.5,
    'examens_programmés' => 45
];

// Liens rapides pour la gestion académique
$liens_gestion_acad = [
    [
        'label' => 'Gérer les Inscriptions',
        'url' => '/admin/gestion-acad/inscriptions',
        'icon' => 'how_to_reg',
        'description' => 'Traiter les nouvelles inscriptions et réinscriptions',
        'badge' => $stats_gestion_acad['inscriptions_en_attente_paiement']
    ],
    [
        'label' => 'Gérer les Notes',
        'url' => '/admin/gestion-acad/notes',
        'icon' => 'grade',
        'description' => 'Saisie et validation des notes d\'examens',
        'badge' => null
    ],
    [
        'label' => 'Gérer les Stages',
        'url' => '/admin/gestion-acad/stages',
        'icon' => 'work',
        'description' => 'Suivi des stages et conventions',
        'badge' => $stats_gestion_acad['stages_en_cours']
    ],
    [
        'label' => 'Gérer les UEs',
        'url' => '/admin/gestion-acad/ues',
        'icon' => 'menu_book',
        'description' => 'Configuration des Unités d\'Enseignement',
        'badge' => null
    ],
    [
        'label' => 'Gérer les ECUEs',
        'url' => '/admin/gestion-acad/ecues',
        'icon' => 'auto_stories',
        'description' => 'Éléments Constitutifs des UEs',
        'badge' => null
    ],
    [
        'label' => 'Documents Scolarité',
        'url' => '/personnel/scolarite/generation-documents',
        'icon' => 'description',
        'description' => 'Générer relevés, attestations, diplômes',
        'badge' => null
    ]
];

// Activités récentes spécifiques à la gestion académique
$activites_recentes = $data['activites_recentes'] ?? [
    [
        'type' => 'inscription',
        'title' => 'Nouvelle inscription validée',
        'description' => 'Marie DUPONT - Master 1 Informatique',
        'time' => '2024-01-15 14:30:00',
        'icon' => 'person_add',
        'color' => 'success'
    ],
    [
        'type' => 'note',
        'title' => 'Notes saisies',
        'description' => 'Mathématiques L2 - 24 étudiants',
        'time' => '2024-01-15 11:15:00',
        'icon' => 'grade',
        'color' => 'info'
    ],
    [
        'type' => 'stage',
        'title' => 'Convention de stage signée',
        'description' => 'Pierre MARTIN chez TechCorp',
        'time' => '2024-01-15 09:45:00',
        'icon' => 'work',
        'color' => 'warning'
    ],
    [
        'type' => 'document',
        'title' => 'Relevé de notes généré',
        'description' => 'Licence 3 - Semestre 5',
        'time' => '2024-01-14 16:20:00',
        'icon' => 'description',
        'color' => 'secondary'
    ]
];

// Alertes importantes
$alertes_importantes = $data['alertes_importantes'] ?? [
    [
        'type' => 'warning',
        'title' => 'Inscriptions en attente',
        'message' => $stats_gestion_acad['inscriptions_en_attente_paiement'] . ' inscriptions nécessitent une validation de paiement',
        'action_url' => '/admin/gestion-acad/inscriptions?filter=pending_payment',
        'action_label' => 'Traiter maintenant'
    ],
    [
        'type' => 'info',
        'title' => 'Période d\'examens',
        'message' => 'La session d\'examens débute dans 2 semaines. Vérifiez la programmation.',
        'action_url' => '/admin/gestion-acad/examens',
        'action_label' => 'Voir le planning'
    ]
];

// Données pour les graphiques
$graphique_inscriptions = $data['graphique_inscriptions'] ?? [
    'labels' => ['Sept', 'Oct', 'Nov', 'Déc', 'Jan'],
    'nouvelles' => [45, 23, 67, 89, 23],
    'reinscriptions' => [120, 98, 145, 167, 89]
];
?>

<div class="admin-module-container">

    <!-- En-tête du module -->
    <div class="module-header">
        <div class="module-header-content">
            <div class="module-header-left">
                <h1 class="admin-title">
                    <span class="material-icons">school</span>
                    Gestion Académique
                </h1>
                <p class="module-subtitle">
                    Pilotage des inscriptions, cursus et suivi pédagogique
                </p>
            </div>
            <div class="module-header-right">
                <div class="module-stats-summary">
                    <div class="summary-item">
                        <span class="summary-number"><?= number_format($stats_gestion_acad['total_etudiants_inscrits']) ?></span>
                        <span class="summary-label">Étudiants</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-number"><?= $stats_gestion_acad['total_ues'] ?></span>
                        <span class="summary-label">UEs</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes importantes -->
    <?php if (!empty($alertes_importantes)): ?>
        <div class="alerts-section mb-6">
            <?php foreach ($alertes_importantes as $alerte): ?>
                <div class="admin-alert <?= e($alerte['type']) ?>">
                    <span class="material-icons">
                        <?= $alerte['type'] === 'warning' ? 'warning' : 'info' ?>
                    </span>
                    <div class="admin-alert-content">
                        <div class="admin-alert-title"><?= e($alerte['title']) ?></div>
                        <div class="admin-alert-text"><?= e($alerte['message']) ?></div>
                    </div>
                    <div class="admin-alert-actions">
                        <a href="<?= e($alerte['action_url']) ?>" class="btn btn-outline btn-sm">
                            <?= e($alerte['action_label']) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques principales -->
    <div class="admin-stats-grid mb-6">
        <div class="admin-stat-card success">
            <div class="admin-stat-header">
                <h3 class="admin-stat-label">Étudiants Inscrits</h3>
                <div class="admin-stat-icon success">
                    <span class="material-icons">school</span>
                </div>
            </div>
            <div class="admin-stat-value" data-counter="<?= $stats_gestion_acad['total_etudiants_inscrits'] ?>">
                <?= number_format($stats_gestion_acad['total_etudiants_inscrits']) ?>
            </div>
            <div class="admin-stat-trend positive">
                <span class="material-icons">trending_up</span>
                +<?= $stats_gestion_acad['nouveaux_inscrits_semaine'] ?> cette semaine
            </div>
        </div>

        <div class="admin-stat-card info">
            <div class="admin-stat-header">
                <h3 class="admin-stat-label">UEs Actives</h3>
                <div class="admin-stat-icon info">
                    <span class="material-icons">menu_book</span>
                </div>
            </div>
            <div class="admin-stat-value"><?= $stats_gestion_acad['total_ues'] ?></div>
            <div class="admin-stat-trend">
                <?= $stats_gestion_acad['total_ecues'] ?> ECUEs associées
            </div>
        </div>

        <div class="admin-stat-card warning">
            <div class="admin-stat-header">
                <h3 class="admin-stat-label">Stages en Cours</h3>
                <div class="admin-stat-icon warning">
                    <span class="material-icons">work</span>
                </div>
            </div>
            <div class="admin-stat-value"><?= $stats_gestion_acad['stages_en_cours'] ?></div>
            <div class="admin-stat-trend">
                Conventions actives
            </div>
        </div>

        <div class="admin-stat-card success">
            <div class="admin-stat-header">
                <h3 class="admin-stat-label">Taux de Réussite</h3>
                <div class="admin-stat-icon success">
                    <span class="material-icons">trending_up</span>
                </div>
            </div>
            <div class="admin-stat-value"><?= $stats_gestion_acad['taux_reussite_global'] ?>%</div>
            <div class="admin-stat-trend positive">
                <span class="material-icons">trending_up</span>
                +2.3% vs année dernière
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="admin-card mb-6">
        <div class="admin-card-header">
            <h2 class="admin-card-title">
                <span class="material-icons">flash_on</span>
                Actions Rapides
            </h2>
            <div class="admin-card-actions">
                <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="refreshQuickActions()">
                    <span class="material-icons">refresh</span>
                    Actualiser
                </button>
            </div>
        </div>

        <div class="quick-actions-grid">
            <?php foreach ($liens_gestion_acad as $action): ?>
                <a href="<?= e($action['url']) ?>" class="quick-action-card">
                    <div class="quick-action-icon">
                        <span class="material-icons"><?= e($action['icon']) ?></span>
                        <?php if ($action['badge']): ?>
                            <span class="quick-action-badge"><?= $action['badge'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="quick-action-content">
                        <h3 class="quick-action-title"><?= e($action['label']) ?></h3>
                        <p class="quick-action-description"><?= e($action['description']) ?></p>
                    </div>
                    <div class="quick-action-arrow">
                        <span class="material-icons">arrow_forward</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Contenu principal en deux colonnes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Activités récentes -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <span class="material-icons">history</span>
                    Activités Récentes
                </h3>
                <button class="admin-btn admin-btn-ghost admin-btn-sm" onclick="refreshActivities()">
                    <span class="material-icons">refresh</span>
                </button>
            </div>

            <div class="activities-list">
                <?php foreach ($activites_recentes as $activite): ?>
                    <div class="activity-item">
                        <div class="activity-icon <?= e($activite['color']) ?>">
                            <span class="material-icons"><?= e($activite['icon']) ?></span>
                        </div>
                        <div class="activity-content">
                            <h4 class="activity-title"><?= e($activite['title']) ?></h4>
                            <p class="activity-description"><?= e($activite['description']) ?></p>
                            <div class="activity-time">
                                <span class="material-icons">schedule</span>
                                <?= date('d/m/Y H:i', strtotime($activite['time'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-footer">
                <a href="/admin/gestion-acad/activities" class="btn btn-outline btn-sm w-full">
                    <span class="material-icons">visibility</span>
                    Voir toutes les activités
                </a>
            </div>
        </div>

        <!-- Statistiques avancées -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <span class="material-icons">insights</span>
                    Évolution des Inscriptions
                </h3>
                <div class="card-header-actions">
                    <select class="form-select admin-btn-sm" onchange="updateChart(this.value)">
                        <option value="6months">6 derniers mois</option>
                        <option value="year">Cette année</option>
                        <option value="all">Toutes les données</option>
                    </select>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="inscriptionsChart" width="400" height="300"></canvas>
            </div>

            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color" style="background: var(--primary-accent);"></span>
                    <span>Nouvelles inscriptions</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: var(--secondary-accent);"></span>
                    <span>Réinscriptions</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateurs de performance -->
    <div class="admin-card mt-6">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <span class="material-icons">analytics</span>
                Indicateurs de Performance
            </h3>
            <div class="card-header-actions">
                <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="exportIndicators()">
                    <span class="material-icons">download</span>
                    Exporter
                </button>
            </div>
        </div>

        <div class="indicators-grid">
            <div class="indicator-item">
                <div class="indicator-label">Délai moyen de traitement des inscriptions</div>
                <div class="indicator-value">2.3 jours</div>
                <div class="indicator-trend positive">-0.8 jour vs mois dernier</div>
            </div>

            <div class="indicator-item">
                <div class="indicator-label">Taux de satisfaction des étudiants</div>
                <div class="indicator-value">94%</div>
                <div class="indicator-trend positive">+3% vs semestre dernier</div>
            </div>

            <div class="indicator-item">
                <div class="indicator-label">Nombre moyen d'UEs par étudiant</div>
                <div class="indicator-value">6.2</div>
                <div class="indicator-trend neutral">Stable</div>
            </div>

            <div class="indicator-item">
                <div class="indicator-label">Taux d'abandon</div>
                <div class="indicator-value">8.5%</div>
                <div class="indicator-trend negative">+1.2% vs année dernière</div>
            </div>
        </div>
    </div>

    <!-- Raccourcis vers les rapports -->
    <div class="admin-card mt-6">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <span class="material-icons">assessment</span>
                Rapports Rapides
            </h3>
        </div>

        <div class="reports-grid">
            <button class="report-btn" onclick="generateReport('inscriptions')">
                <span class="material-icons">people</span>
                <span>Rapport Inscriptions</span>
            </button>

            <button class="report-btn" onclick="generateReport('notes')">
                <span class="material-icons">grade</span>
                <span>Synthèse des Notes</span>
            </button>

            <button class="report-btn" onclick="generateReport('stages')">
                <span class="material-icons">work</span>
                <span>Suivi des Stages</span>
            </button>

            <button class="report-btn" onclick="generateReport('ues')">
                <span class="material-icons">menu_book</span>
                <span>État des UEs</span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation des compteurs
        animateCounters();

        // Initialiser le graphique
        if (typeof Chart !== 'undefined') {
            initInscriptionsChart();
        }

        // Auto-refresh des données toutes les 5 minutes
        setInterval(refreshDashboardData, 300000);
    });

    function animateCounters() {
        document.querySelectorAll('[data-counter]').forEach(counter => {
            const target = parseInt(counter.getAttribute('data-counter'));
            if (isNaN(target)) return;

            let current = 0;
            const increment = Math.ceil(target / 50);
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    counter.textContent = current.toLocaleString();
                }
            }, 30);
        });
    }

    function initInscriptionsChart() {
        const ctx = document.getElementById('inscriptionsChart');
        if (!ctx) return;

        window.inscriptionsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($graphique_inscriptions['labels']) ?>,
                datasets: [
                    {
                        label: 'Nouvelles inscriptions',
                        data: <?= json_encode($graphique_inscriptions['nouvelles']) ?>,
                        borderColor: 'var(--primary-accent)',
                        backgroundColor: 'rgba(40, 183, 7, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Réinscriptions',
                        data: <?= json_encode($graphique_inscriptions['reinscriptions']) ?>,
                        borderColor: 'var(--secondary-accent)',
                        backgroundColor: 'rgba(3, 105, 161, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 6,
                        hoverRadius: 8
                    }
                }
            }
        });
    }

    function updateChart(period) {
        // Mise à jour du graphique selon la période sélectionnée
        fetch(`/api/gestion-acad/chart-data?period=${period}`, {
            headers: {
                'X-CSRF-Token': window.AppConfig?.csrfToken || ''
            }
        })
            .then(response => response.json())
            .then(data => {
                if (window.inscriptionsChart) {
                    window.inscriptionsChart.data.labels = data.labels;
                    window.inscriptionsChart.data.datasets[0].data = data.nouvelles;
                    window.inscriptionsChart.data.datasets[1].data = data.reinscriptions;
                    window.inscriptionsChart.update();
                }
            })
            .catch(error => {
                console.error('Erreur mise à jour graphique:', error);
                window.GestionMySoutenance?.showFlashMessage('error', 'Erreur lors de la mise à jour du graphique');
            });
    }

    function refreshQuickActions() {
        // Animation du bouton de rafraîchissement
        const button = event.target.closest('button');
        const icon = button.querySelector('.material-icons');
        icon.style.animation = 'spin 1s linear infinite';

        // Simuler le rafraîchissement
        setTimeout(() => {
            icon.style.animation = '';
            window.GestionMySoutenance?.showFlashMessage('success', 'Actions rapides mises à jour');
        }, 1000);
    }

    function refreshActivities() {
        const button = event.target.closest('button');
        const icon = button.querySelector('.material-icons');

        icon.style.animation = 'spin 1s linear infinite';

        fetch('/api/gestion-acad/recent-activities', {
            headers: {
                'X-CSRF-Token': window.AppConfig?.csrfToken || ''
            }
        })
            .then(response => response.json())
            .then(data => {
                updateActivitiesList(data.activities);
                window.GestionMySoutenance?.showFlashMessage('success', 'Activités mises à jour');
            })
            .catch(error => {
                console.error('Erreur:', error);
                window.GestionMySoutenance?.showFlashMessage('error', 'Erreur lors de la mise à jour');
            })
            .finally(() => {
                icon.style.animation = '';
            });
    }

    function updateActivitiesList(activities) {
        // Mettre à jour la liste des activités
        const container = document.querySelector('.activities-list');
        if (container && activities) {
            // Implémenter la mise à jour de la liste
            console.log('Nouvelles activités:', activities);
        }
    }

    function refreshDashboardData() {
        // Rafraîchissement silencieux des données
        fetch('/api/gestion-acad/dashboard-refresh', {
            headers: {
                'X-CSRF-Token': window.AppConfig?.csrfToken || ''
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.stats) {
                    updateStats(data.stats);
                }
            })
            .catch(error => {
                console.error('Erreur rafraîchissement:', error);
            });
    }

    function updateStats(stats) {
        // Mettre à jour les statistiques
        Object.entries(stats).forEach(([key, value]) => {
            const element = document.querySelector(`[data-counter] `);
            // Implémenter la mise à jour des stats
        });
    }

    function generateReport(type) {
        window.GestionMySoutenance?.showFlashMessage('info', `Génération du rapport ${type} en cours...`);

        // Rediriger vers la page de génération de rapport
        setTimeout(() => {
            window.location.href = `/admin/gestion-acad/reports/${type}`;
        }, 1000);
    }

    function exportIndicators() {
        window.GestionMySoutenance?.showFlashMessage('info', 'Export des indicateurs en cours...');

        // Simuler l'export
        setTimeout(() => {
            const link = document.createElement('a');
            link.href = '/api/gestion-acad/export-indicators';
            link.download = `indicateurs-${new Date().toISOString().split('T')[0]}.csv`;
            link.click();

            window.GestionMySoutenance?.showFlashMessage('success', 'Export terminé');
        }, 2000);
    }
</script>

<style>
    /* Styles spécifiques à la gestion académique */
    .module-header {
        background: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
        color: white;
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-2xl);
        margin-bottom: var(--spacing-xl);
    }

    .module-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--spacing-lg);
    }

    .module-subtitle {
        font-size: var(--font-size-lg);
        opacity: 0.9;
        margin: var(--spacing-sm) 0 0 0;
    }

    .module-stats-summary {
        display: flex;
        gap: var(--spacing-xl);
    }

    .summary-item {
        text-align: center;
        background: rgba(255, 255, 255, 0.15);
        padding: var(--spacing-lg);
        border-radius: var(--border-radius-lg);
        backdrop-filter: blur(10px);
    }

    .summary-number {
        display: block;
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        margin-bottom: var(--spacing-xs);
    }

    .summary-label {
        font-size: var(--font-size-sm);
        opacity: 0.9;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-lg);
    }

    .quick-action-card {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
        padding: var(--spacing-xl);
        background: white;
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-lg);
        text-decoration: none;
        color: var(--text-primary);
        transition: all var(--transition-fast);
        position: relative;
        overflow: hidden;
    }

    .quick-action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary-accent);
        transform: scaleY(0);
        transition: transform var(--transition-fast);
    }

    .quick-action-card:hover {
        background: var(--hover-bg);
        border-color: var(--primary-accent);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        text-decoration: none;
        color: var(--text-primary);
    }

    .quick-action-card:hover::before {
        transform: scaleY(1);
    }

    .quick-action-icon {
        position: relative;
        width: 56px;
        height: 56px;
        background: var(--primary-accent-light);
        color: var(--primary-accent);
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .quick-action-icon .material-icons {
        font-size: 1.75rem;
    }

    .quick-action-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: var(--danger-accent);
        color: white;
        border-radius: var(--border-radius-full);
        padding: 2px 6px;
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-bold);
        min-width: 20px;
        text-align: center;
        border: 2px solid white;
    }

    .quick-action-content {
        flex: 1;
    }

    .quick-action-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-xs) 0;
    }

    .quick-action-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.4;
    }

    .quick-action-arrow {
        color: var(--text-muted);
        transition: all var(--transition-fast);
    }

    .quick-action-card:hover .quick-action-arrow {
        color: var(--primary-accent);
        transform: translateX(4px);
    }

    .activities-list {
        max-height: 350px;
        overflow-y: auto;
    }

    .chart-container {
        height: 300px;
        padding: var(--spacing-lg);
    }

    .chart-legend {
        display: flex;
        justify-content: center;
        gap: var(--spacing-lg);
        padding: var(--spacing-md) var(--spacing-lg);
        border-top: 1px solid var(--border-light);
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: var(--font-size-sm);
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }

    .indicators-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
    }

    .indicator-item {
        padding: var(--spacing-lg);
        background: var(--hover-bg);
        border-radius: var(--border-radius-lg);
        text-align: center;
    }

    .indicator-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-sm);
    }

    .indicator-value {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
    }

    .indicator-trend {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-medium);
    }

    .indicator-trend.positive {
        color: var(--primary-accent);
    }

    .indicator-trend.negative {
        color: var(--danger-accent);
    }

    .indicator-trend.neutral {
        color: var(--text-muted);
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .report-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-lg);
        background: white;
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-lg);
        cursor: pointer;
        transition: all var(--transition-fast);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
    }

    .report-btn:hover {
        background: var(--primary-accent-light);
        border-color: var(--primary-accent);
        color: var(--primary-accent-dark);
        transform: translateY(-2px);
    }

    .report-btn .material-icons {
        font-size: 2rem;
        color: var(--primary-accent);
    }

    .card-footer {
        border-top: 1px solid var(--border-light);
        padding: var(--spacing-lg);
        background: var(--hover-bg);
        border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
    }

    .card-header-actions {
        display: flex;
        gap: var(--spacing-sm);
        align-items: center;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .module-header-content {
            flex-direction: column;
            text-align: center;
        }

        .module-stats-summary {
            width: 100%;
            justify-content: center;
        }

        .quick-actions-grid {
            grid-template-columns: 1fr;
        }

        .quick-action-card {
            flex-direction: column;
            text-align: center;
        }

        .indicators-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .reports-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .indicators-grid,
        .reports-grid {
            grid-template-columns: 1fr;
        }

        .summary-item {
            padding: var(--spacing-md);
        }

        .summary-number {
            font-size: var(--font-size-xl);
        }
    }

    /* Animation spin pour les boutons refresh */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>