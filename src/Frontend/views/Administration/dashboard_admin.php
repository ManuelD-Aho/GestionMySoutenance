<?php
/**
 * Dashboard principal modernisé - GestionMySoutenance
 * Page d'accueil avec statistiques et actions rapides
 * Fichier: src/Frontend/views/Dashboard/index.php
 */

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Récupération des données (normalement depuis le contrôleur)
$user_role = $_SESSION['user_role'] ?? 'guest';
$current_user = $current_user ?? $_SESSION['user_data'] ?? null;
$user_name = $current_user['nom'] ?? $current_user['name'] ?? 'Utilisateur';

// Statistiques générales (à adapter selon vos besoins)
$dashboard_stats = $data['dashboard_stats'] ?? [
    'total_students' => 1247,
    'active_courses' => 89,
    'upcoming_exams' => 24,
    'success_rate' => 87,
    'pending_tasks' => 12,
    'system_load' => 68
];

// Notifications/alertes importantes
$system_alerts = $data['system_alerts'] ?? [
    [
        'type' => 'warning',
        'title' => 'Maintenance programmée',
        'message' => 'Une maintenance est prévue le 20 janvier de 2h00 à 4h00. Les services seront temporairement indisponibles.',
        'icon' => 'warning',
        'priority' => 'high'
    ]
];

// Activités récentes
$recent_activities = $data['recent_activities'] ?? [
    [
        'type' => 'user_registered',
        'title' => 'Nouvel utilisateur inscrit',
        'description' => 'Marie Dubois s\'est inscrite',
        'time' => '2024-01-15 10:30:00',
        'icon' => 'person_add',
        'color' => 'success'
    ],
    [
        'type' => 'exam_validated',
        'title' => 'Examen validé',
        'description' => 'Mathématiques L1 - Résultats publiés',
        'time' => '2024-01-15 09:15:00',
        'icon' => 'assignment_turned_in',
        'color' => 'info'
    ],
    [
        'type' => 'system_alert',
        'title' => 'Alerte système',
        'description' => 'Espace disque faible sur le serveur',
        'time' => '2024-01-15 08:45:00',
        'icon' => 'warning',
        'color' => 'warning'
    ]
];

// Tâches à faire selon le rôle
$todo_tasks = $data['todo_tasks'] ?? [];

// Actions rapides selon le rôle
$quick_actions = [];

if ($user_role === 'admin') {
    $quick_actions = [
        ['label' => 'Ajouter un Utilisateur', 'url' => '/admin/users/create', 'icon' => 'person_add', 'color' => 'primary'],
        ['label' => 'Créer un Cours', 'url' => '/admin/courses/create', 'icon' => 'add_circle', 'color' => 'success'],
        ['label' => 'Programmer un Examen', 'url' => '/admin/exams/create', 'icon' => 'event', 'color' => 'info'],
        ['label' => 'Générer un Rapport', 'url' => '/admin/reports', 'icon' => 'assessment', 'color' => 'secondary'],
        ['label' => 'Paramètres Système', 'url' => '/admin/settings', 'icon' => 'settings', 'color' => 'warning'],
        ['label' => 'Sauvegarde', 'url' => '/admin/backup', 'icon' => 'backup', 'color' => 'info']
    ];

    $todo_tasks = [
        [
            'title' => 'Valider les notes de philosophie',
            'description' => 'Examens en attente de validation',
            'deadline' => 'Aujourd\'hui',
            'priority' => 'high',
            'url' => '/admin/grades/pending'
        ],
        [
            'title' => 'Préparer le planning des examens',
            'description' => 'Session de février 2024',
            'deadline' => 'Demain',
            'priority' => 'medium',
            'url' => '/admin/exams/schedule'
        ],
        [
            'title' => 'Mise à jour documentation',
            'description' => 'Guide utilisateur à réviser',
            'deadline' => 'Cette semaine',
            'priority' => 'low',
            'url' => '/admin/documentation'
        ]
    ];
} elseif ($user_role === 'enseignant') {
    $quick_actions = [
        ['label' => 'Mes Cours', 'url' => '/teacher/courses', 'icon' => 'class', 'color' => 'primary'],
        ['label' => 'Nouvelle Évaluation', 'url' => '/teacher/evaluations/create', 'icon' => 'quiz', 'color' => 'success'],
        ['label' => 'Saisir des Notes', 'url' => '/teacher/grades', 'icon' => 'grade', 'color' => 'info'],
        ['label' => 'Planning', 'url' => '/teacher/schedule', 'icon' => 'event', 'color' => 'secondary']
    ];

    $todo_tasks = [
        [
            'title' => 'Corriger les copies de mathématiques',
            'description' => '24 copies en attente',
            'deadline' => 'Vendredi',
            'priority' => 'high',
            'url' => '/teacher/grading/pending'
        ],
        [
            'title' => 'Préparer le cours de demain',
            'description' => 'Algèbre linéaire - Chapitre 5',
            'deadline' => 'Demain 8h',
            'priority' => 'medium',
            'url' => '/teacher/courses/prepare'
        ]
    ];
} elseif ($user_role === 'etudiant') {
    $quick_actions = [
        ['label' => 'Mes Cours', 'url' => '/student/courses', 'icon' => 'book', 'color' => 'primary'],
        ['label' => 'Mes Notes', 'url' => '/student/grades', 'icon' => 'grade', 'color' => 'success'],
        ['label' => 'Planning', 'url' => '/student/schedule', 'icon' => 'event', 'color' => 'info'],
        ['label' => 'Documents', 'url' => '/student/documents', 'icon' => 'description', 'color' => 'secondary']
    ];

    $todo_tasks = [
        [
            'title' => 'Inscription aux examens',
            'description' => 'Session de février 2024',
            'deadline' => '31 janvier',
            'priority' => 'high',
            'url' => '/student/exam-registration'
        ],
        [
            'title' => 'Rendre le projet de programmation',
            'description' => 'Projet Java - Application web',
            'deadline' => 'Vendredi',
            'priority' => 'medium',
            'url' => '/student/assignments'
        ]
    ];
}

// Données pour les graphiques (si Chart.js est disponible)
$chart_data = $data['chart_data'] ?? [
    'user_evolution' => [
        'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
        'data' => [120, 135, 158, 142, 167, 189]
    ],
    'course_completion' => [
        'completed' => 68,
        'in_progress' => 25,
        'not_started' => 7
    ]
];
?>

<div class="admin-container">

    <!-- En-tête de la page -->
    <div class="page-header mb-6">
        <div class="page-header-content">
            <div class="page-header-left">
                <h1 class="page-title">
                    <?php
                    $greetings = [
                        'matin' => 'Bonjour',
                        'apres-midi' => 'Bon après-midi',
                        'soir' => 'Bonsoir'
                    ];
                    $hour = (int)date('H');
                    $greeting = $hour < 12 ? $greetings['matin'] : ($hour < 18 ? $greetings['apres-midi'] : $greetings['soir']);
                    ?>
                    <?= $greeting ?>, <?= e($user_name) ?> !
                </h1>
                <p class="page-subtitle">
                    Voici un aperçu de votre espace GestionMySoutenance
                </p>
            </div>
            <div class="page-header-right">
                <div class="date-display">
                    <span class="material-icons">today</span>
                    <span><?= date('d F Y') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes système importantes -->
    <?php if (!empty($system_alerts)): ?>
        <?php foreach ($system_alerts as $alert): ?>
            <div class="admin-alert <?= e($alert['type']) ?> mb-4">
                <span class="material-icons"><?= e($alert['icon']) ?></span>
                <div class="admin-alert-content">
                    <div class="admin-alert-title"><?= e($alert['title']) ?></div>
                    <div class="admin-alert-text"><?= e($alert['message']) ?></div>
                </div>
                <button class="alert-close" onclick="dismissAlert(this)">
                    <span class="material-icons">close</span>
                </button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Statistiques principales -->
    <div class="stats-grid mb-6">
        <?php if ($user_role === 'admin'): ?>
            <div class="stat-card" data-stat="total-students">
                <div class="stat-header">
                    <h3 class="stat-label">Étudiants Inscrits</h3>
                    <div class="stat-icon icon-bg-blue">
                        <span class="material-icons">school</span>
                    </div>
                </div>
                <p class="stat-value" data-counter="<?= $dashboard_stats['total_students'] ?>"><?= number_format($dashboard_stats['total_students']) ?></p>
                <div class="stat-change positive">+5% ce mois</div>
            </div>

            <div class="stat-card" data-stat="active-courses">
                <div class="stat-header">
                    <h3 class="stat-label">Cours Actifs</h3>
                    <div class="stat-icon icon-bg-green">
                        <span class="material-icons">book</span>
                    </div>
                </div>
                <p class="stat-value" data-counter="<?= $dashboard_stats['active_courses'] ?>"><?= number_format($dashboard_stats['active_courses']) ?></p>
                <div class="stat-change">+2 nouveaux</div>
            </div>

            <div class="stat-card" data-stat="upcoming-exams">
                <div class="stat-header">
                    <h3 class="stat-label">Examens Programmés</h3>
                    <div class="stat-icon icon-bg-yellow">
                        <span class="material-icons">quiz</span>
                    </div>
                </div>
                <p class="stat-value" data-counter="<?= $dashboard_stats['upcoming_exams'] ?>"><?= number_format($dashboard_stats['upcoming_exams']) ?></p>
                <div class="stat-change">Cette semaine</div>
            </div>

            <div class="stat-card" data-stat="success-rate">
                <div class="stat-header">
                    <h3 class="stat-label">Taux de Réussite</h3>
                    <div class="stat-icon icon-bg-violet">
                        <span class="material-icons">trending_up</span>
                    </div>
                </div>
                <p class="stat-value" data-counter="<?= $dashboard_stats['success_rate'] ?>"><?= $dashboard_stats['success_rate'] ?>%</p>
                <div class="stat-change positive">+3% vs année dernière</div>
            </div>

        <?php elseif ($user_role === 'enseignant'): ?>
            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Mes Cours</h3>
                    <div class="stat-icon icon-bg-blue">
                        <span class="material-icons">class</span>
                    </div>
                </div>
                <p class="stat-value">6</p>
                <div class="stat-change">Ce semestre</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Étudiants</h3>
                    <div class="stat-icon icon-bg-green">
                        <span class="material-icons">groups</span>
                    </div>
                </div>
                <p class="stat-value">142</p>
                <div class="stat-change">Total inscrits</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Évaluations</h3>
                    <div class="stat-icon icon-bg-yellow">
                        <span class="material-icons">quiz</span>
                    </div>
                </div>
                <p class="stat-value">8</p>
                <div class="stat-change">En attente</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Moyenne Classe</h3>
                    <div class="stat-icon icon-bg-violet">
                        <span class="material-icons">grade</span>
                    </div>
                </div>
                <p class="stat-value">14.2</p>
                <div class="stat-change positive">+0.5 vs dernier semestre</div>
            </div>

        <?php elseif ($user_role === 'etudiant'): ?>
            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Mes Cours</h3>
                    <div class="stat-icon icon-bg-blue">
                        <span class="material-icons">book</span>
                    </div>
                </div>
                <p class="stat-value">8</p>
                <div class="stat-change">Ce semestre</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Moyenne Générale</h3>
                    <div class="stat-icon icon-bg-green">
                        <span class="material-icons">grade</span>
                    </div>
                </div>
                <p class="stat-value">15.2</p>
                <div class="stat-change positive">+0.8 vs dernier semestre</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Prochains Examens</h3>
                    <div class="stat-icon icon-bg-yellow">
                        <span class="material-icons">event</span>
                    </div>
                </div>
                <p class="stat-value">3</p>
                <div class="stat-change">Cette semaine</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-label">Progression</h3>
                    <div class="stat-icon icon-bg-violet">
                        <span class="material-icons">trending_up</span>
                    </div>
                </div>
                <p class="stat-value">78%</p>
                <div class="stat-change">Du programme</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Actions rapides -->
    <?php if (!empty($quick_actions)): ?>
        <div class="card mb-6">
            <div class="card-header">
                <h2 class="card-title">
                    <span class="material-icons">flash_on</span>
                    Actions Rapides
                </h2>
            </div>

            <div class="quick-links-grid">
                <?php foreach ($quick_actions as $action): ?>
                    <a href="<?= e($action['url']) ?>" class="quick-action-btn">
                        <span class="material-icons"><?= e($action['icon']) ?></span>
                        <span><?= e($action['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Activités récentes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="material-icons">history</span>
                    Activités Récentes
                </h3>
                <button class="btn btn-ghost btn-sm" onclick="refreshActivities()">
                    <span class="material-icons">refresh</span>
                </button>
            </div>

            <div class="activity-list">
                <?php if (empty($recent_activities)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <span class="material-icons">history</span>
                        </div>
                        <p class="empty-state-title">Aucune activité récente</p>
                        <p class="empty-state-description">Les activités apparaîtront ici</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= e($activity['color']) ?>">
                                <span class="material-icons"><?= e($activity['icon']) ?></span>
                            </div>
                            <div class="activity-content">
                                <h4 class="activity-title"><?= e($activity['title']) ?></h4>
                                <p class="activity-description"><?= e($activity['description']) ?></p>
                                <div class="activity-time">
                                    <span class="material-icons">access_time</span>
                                    <?= date('d/m/Y H:i', strtotime($activity['time'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tâches à faire -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="material-icons">task_alt</span>
                    Tâches à Faire
                    <?php if (!empty($todo_tasks)): ?>
                        <span class="badge badge-warning ml-2"><?= count($todo_tasks) ?></span>
                    <?php endif; ?>
                </h3>
            </div>

            <div class="todo-list">
                <?php if (empty($todo_tasks)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <span class="material-icons">task_alt</span>
                        </div>
                        <p class="empty-state-title">Aucune tâche en attente</p>
                        <p class="empty-state-description">Vous êtes à jour ! 🎉</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todo_tasks as $task): ?>
                        <div class="todo-item priority-<?= e($task['priority']) ?>">
                            <div class="todo-content">
                                <h4 class="todo-title"><?= e($task['title']) ?></h4>
                                <p class="todo-description"><?= e($task['description']) ?></p>
                                <div class="todo-meta">
                                    <span class="todo-deadline">
                                        <span class="material-icons">schedule</span>
                                        Échéance: <?= e($task['deadline']) ?>
                                    </span>
                                    <span class="todo-priority priority-<?= e($task['priority']) ?>">
                                        <?= ucfirst($task['priority']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="todo-actions">
                                <a href="<?= e($task['url']) ?>" class="btn btn-primary btn-sm">
                                    <span class="material-icons">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Graphiques (si admin) -->
    <?php if ($user_role === 'admin'): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="material-icons">trending_up</span>
                        Évolution des Inscriptions
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="userChart" width="400" height="200"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="material-icons">pie_chart</span>
                        Répartition des Cours
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="courseChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Liens rapides vers les modules (pour les rôles appropriés) -->
    <?php if ($user_role === 'admin'): ?>
        <div class="card mt-6">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="material-icons">apps</span>
                    Modules d'Administration
                </h3>
            </div>

            <div class="modules-grid">
                <a href="/admin/users" class="module-card">
                    <div class="module-icon">
                        <span class="material-icons">people</span>
                    </div>
                    <div class="module-content">
                        <h4 class="module-title">Gestion Utilisateurs</h4>
                        <p class="module-description">Créer, modifier et gérer les comptes utilisateurs</p>
                    </div>
                    <div class="module-arrow">
                        <span class="material-icons">arrow_forward</span>
                    </div>
                </a>

                <a href="/admin/gestion-acad" class="module-card">
                    <div class="module-icon">
                        <span class="material-icons">school</span>
                    </div>
                    <div class="module-content">
                        <h4 class="module-title">Gestion Académique</h4>
                        <p class="module-description">Inscriptions, notes, stages et programmes</p>
                    </div>
                    <div class="module-arrow">
                        <span class="material-icons">arrow_forward</span>
                    </div>
                </a>

                <a href="/admin/courses" class="module-card">
                    <div class="module-icon">
                        <span class="material-icons">menu_book</span>
                    </div>
                    <div class="module-content">
                        <h4 class="module-title">Cours & Programmes</h4>
                        <p class="module-description">Créer et organiser les cours et programmes</p>
                    </div>
                    <div class="module-arrow">
                        <span class="material-icons">arrow_forward</span>
                    </div>
                </a>

                <a href="/admin/settings" class="module-card">
                    <div class="module-icon">
                        <span class="material-icons">settings</span>
                    </div>
                    <div class="module-content">
                        <h4 class="module-title">Configuration</h4>
                        <p class="module-description">Paramètres système et configuration</p>
                    </div>
                    <div class="module-arrow">
                        <span class="material-icons">arrow_forward</span>
                    </div>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation des compteurs
        animateCounters();

        // Initialiser les graphiques si Chart.js est disponible
        if (typeof Chart !== 'undefined') {
            initCharts();
        }

        // Rafraîchissement automatique des données
        setInterval(refreshDashboardData, 300000); // 5 minutes

        // Marquer les tâches comme vues
        markTasksAsViewed();
    });

    function animateCounters() {
        document.querySelectorAll('[data-counter]').forEach(counter => {
            const target = parseInt(counter.getAttribute('data-counter'));
            if (isNaN(target)) return;

            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current).toLocaleString();
                }
            }, 30);
        });
    }

    function initCharts() {
        // Graphique d'évolution des utilisateurs
        const userCtx = document.getElementById('userChart');
        if (userCtx) {
            new Chart(userCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chart_data['user_evolution']['labels']) ?>,
                    datasets: [{
                        label: 'Nouvelles inscriptions',
                        data: <?= json_encode($chart_data['user_evolution']['data']) ?>,
                        borderColor: '#28b707',
                        backgroundColor: 'rgba(40, 183, 7, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Graphique de répartition des cours
        const courseCtx = document.getElementById('courseChart');
        if (courseCtx) {
            new Chart(courseCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Terminés', 'En cours', 'Non commencés'],
                    datasets: [{
                        data: [
                            <?= $chart_data['course_completion']['completed'] ?>,
                            <?= $chart_data['course_completion']['in_progress'] ?>,
                            <?= $chart_data['course_completion']['not_started'] ?>
                        ],
                        backgroundColor: [
                            '#28b707',
                            '#f59e0b',
                            '#dc2626'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    function refreshActivities() {
        const button = event.target.closest('button');
        const icon = button.querySelector('.material-icons');

        // Animation de rotation
        icon.style.animation = 'spin 1s linear infinite';

        // Simuler le chargement
        fetch('/api/dashboard/activities', {
            headers: {
                'X-CSRF-Token': window.AppConfig?.csrfToken || ''
            }
        })
            .then(response => response.json())
            .then(data => {
                // Mettre à jour les activités
                updateActivities(data.activities);
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

    function updateActivities(activities) {
        // Implémenter la mise à jour des activités
        console.log('Nouvelles activités:', activities);
    }

    function refreshDashboardData() {
        // Rafraîchissement automatique en arrière-plan
        fetch('/api/dashboard/refresh', {
            headers: {
                'X-CSRF-Token': window.AppConfig?.csrfToken || ''
            }
        })
            .then(response => response.json())
            .then(data => {
                // Mettre à jour les statistiques si nécessaire
                updateStats(data.stats);
            })
            .catch(error => {
                console.error('Erreur de rafraîchissement:', error);
            });
    }

    function updateStats(stats) {
        // Mettre à jour les statistiques
        Object.entries(stats).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"] .stat-value`);
            if (element) {
                element.textContent = value.toLocaleString();
            }
        });
    }

    function dismissAlert(button) {
        const alert = button.closest('.admin-alert');
        alert.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }

    function markTasksAsViewed() {
        // Marquer les tâches comme vues pour réduire les notifications
        const taskCount = document.querySelectorAll('.todo-item').length;
        if (taskCount > 0) {
            fetch('/api/tasks/mark-viewed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.AppConfig?.csrfToken || ''
                }
            }).catch(console.error);
        }
    }
</script>

<style>
    /* Styles spécifiques au dashboard */
    .page-header {
        background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
        color: white;
        padding: var(--spacing-2xl);
        border-radius: var(--border-radius-xl);
        margin-bottom: var(--spacing-xl);
    }

    .page-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--spacing-lg);
    }

    .page-title {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        margin: 0;
        margin-bottom: var(--spacing-sm);
    }

    .page-subtitle {
        font-size: var(--font-size-lg);
        opacity: 0.9;
        margin: 0;
    }

    .date-display {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        background: rgba(255, 255, 255, 0.2);
        padding: var(--spacing-md) var(--spacing-lg);
        border-radius: var(--border-radius-lg);
        font-weight: var(--font-weight-medium);
    }

    .activity-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-md);
        padding: var(--spacing-lg);
        border-bottom: 1px solid var(--border-light);
        transition: background-color var(--transition-fast);
    }

    .activity-item:hover {
        background: var(--hover-bg);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .activity-icon.success { background: var(--primary-accent); }
    .activity-icon.info { background: var(--info-accent); }
    .activity-icon.warning { background: var(--warning-accent); }
    .activity-icon.danger { background: var(--danger-accent); }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-xs) 0;
    }

    .activity-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-sm) 0;
    }

    .activity-time {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        font-size: var(--font-size-xs);
        color: var(--text-muted);
    }

    .activity-time .material-icons {
        font-size: 1rem;
    }

    .todo-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .todo-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-lg);
        border-left: 4px solid var(--border-light);
        border-bottom: 1px solid var(--border-light);
        transition: all var(--transition-fast);
    }

    .todo-item:hover {
        background: var(--hover-bg);
    }

    .todo-item:last-child {
        border-bottom: none;
    }

    .todo-item.priority-high {
        border-left-color: var(--danger-accent);
    }

    .todo-item.priority-medium {
        border-left-color: var(--warning-accent);
    }

    .todo-item.priority-low {
        border-left-color: var(--primary-accent);
    }

    .todo-content {
        flex: 1;
    }

    .todo-title {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-xs) 0;
    }

    .todo-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-sm) 0;
    }

    .todo-meta {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        flex-wrap: wrap;
    }

    .todo-deadline {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        font-size: var(--font-size-xs);
        color: var(--text-muted);
    }

    .todo-deadline .material-icons {
        font-size: 1rem;
    }

    .todo-priority {
        padding: 2px 8px;
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-medium);
        text-transform: uppercase;
    }

    .todo-priority.priority-high {
        background: var(--danger-accent-light);
        color: #7f1d1d;
    }

    .todo-priority.priority-medium {
        background: var(--warning-accent-light);
        color: #78350f;
    }

    .todo-priority.priority-low {
        background: var(--primary-accent-light);
        color: var(--primary-accent-dark);
    }

    .chart-container {
        padding: var(--spacing-lg);
        height: 250px;
    }

    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-lg);
    }

    .module-card {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
        padding: var(--spacing-xl);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-lg);
        text-decoration: none;
        color: var(--text-primary);
        transition: all var(--transition-fast);
    }

    .module-card:hover {
        background: var(--hover-bg);
        border-color: var(--primary-accent);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        text-decoration: none;
        color: var(--text-primary);
    }

    .module-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius-lg);
        background: var(--primary-accent-light);
        color: var(--primary-accent);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .module-icon .material-icons {
        font-size: 1.5rem;
    }

    .module-content {
        flex: 1;
    }

    .module-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-xs) 0;
    }

    .module-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    .module-arrow {
        color: var(--text-muted);
        transition: all var(--transition-fast);
    }

    .module-card:hover .module-arrow {
        color: var(--primary-accent);
        transform: translateX(4px);
    }

    .alert-close {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        padding: var(--spacing-xs);
        border-radius: var(--border-radius-md);
        transition: background-color var(--transition-fast);
    }

    .alert-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header-content {
            text-align: center;
        }

        .page-title {
            font-size: var(--font-size-2xl);
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: var(--spacing-md);
        }

        .modules-grid {
            grid-template-columns: 1fr;
        }

        .todo-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-xs);
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .module-card {
            flex-direction: column;
            text-align: center;
        }

        .module-arrow {
            transform: rotate(90deg);
        }

        .module-card:hover .module-arrow {
            transform: rotate(90deg) translateY(4px);
        }
    }
</style>