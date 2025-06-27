<?php
// app/views/etudiant/dashboard.php
// Dashboard Étudiant Amélioré - GestionMySoutenance

// Palette de couleurs du projet
$primaryDark = '#1a3d7c';
$primary = '#2a5caa';
$primaryLight = '#e6f0ff';
$accent = '#d4af37';
$success = '#28a745';
$warning = '#ffc107';
$danger = '#dc3545';
$info = '#17a2b8';
$textDark = '#495057';
$textLight = '#f8f9fa';
$borderColor = '#dee2e6';
$cardBg = '#ffffff';

// Récupération des données avec valeurs par défaut
$etudiant = $data['etudiant'] ?? (object)[
    'numero_carte_etudiant' => '',
    'prenom' => 'Prénom',
    'nom' => 'Nom',
    'email' => 'email@exemple.com',
    'telephone' => '',
    'filiere' => 'Filière non définie',
    'niveau_etude' => 'Master 2',
    'photo_profil_chemin' => null
];

// Données pour les nouvelles fonctionnalités
$rapportEtudiant = $data['rapportEtudiant'] ?? null;
$statutRapport = $data['statutRapport'] ?? null;
$historiqueStatuts = $data['historiqueStatuts'] ?? [];
$notifications = $data['notifications'] ?? [];
$alertes = $data['alertes'] ?? [];
$documentsOfficials = $data['documentsOfficials'] ?? [];
$reclamations = $data['reclamations'] ?? [];
$prochainesEcheances = $data['prochainesEcheances'] ?? [];
$soutenance = $data['soutenance'] ?? null;

// Calcul des statistiques pour le dashboard
$totalNotifications = count($notifications);
$notificationsNonLues = count(array_filter($notifications, function($n) { return !$n->lue; }));
$alertesUrgentes = count(array_filter($alertes, function($a) { return $a->priorite === 'URGENTE'; }));
$reclamationsEnCours = count(array_filter($reclamations, function($r) { return $r->statut !== 'TRAITEE'; }));

// Obtenir l'onglet actif
$ongletActif = $_GET['tab'] ?? 'dashboard';

// URL de base du projet
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/GestionMySoutenance';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace MySoutenance - <?= htmlspecialchars($etudiant->prenom . ' ' . $etudiant->nom) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>

        :root {

            --primary-dark: <?php echo $primaryDark; ?>;

            --primary: <?php echo $primary; ?>;

            --primary-light: <?php echo $primaryLight; ?>;

            --accent: <?php echo $accent; ?>;

            --success: <?php echo $success; ?>;

            --warning: <?php echo $warning; ?>;

            --danger: <?php echo $danger; ?>;
            --info: <?php echo $info; ?>;
            --text-dark: <?php echo $textDark; ?>;
            --text-light: <?php echo $textLight; ?>;
            --border-color: <?php echo $borderColor; ?>;
            --card-bg: <?php echo $cardBg; ?>;
        }
        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

            font-family: 'Poppins', sans-serif;

        }



        body {

            background-color: #f5f7fb;

            color: var(--text-dark);

        }



        .dashboard-container {

            display: grid;

            grid-template-columns: 280px 1fr;

            min-height: 100vh;

        }



        /* Sidebar Styles */

        .sidebar {

            background: linear-gradient(180deg, var(--primary-dark), var(--primary));

            color: white;

            padding: 25px 15px;

            position: fixed;

            width: 280px;

            height: 100vh;

            overflow-y: auto;

            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);

        }



        .logo {

            display: flex;

            align-items: center;

            padding: 0 10px 25px 10px;

            border-bottom: 1px solid rgba(255, 255, 255, 0.1);

        }



        .logo i {

            font-size: 32px;

            margin-right: 12px;

            color: var(--accent);

        }



        .logo h1 {

            font-size: 20px;

            font-weight: 600;

            line-height: 1.2;

        }



        .user-profile-sidebar {

            padding: 20px 10px;

            border-bottom: 1px solid rgba(255, 255, 255, 0.1);

            text-align: center;

        }



        .avatar-sidebar {

            width: 60px;

            height: 60px;

            border-radius: 50%;

            background: linear-gradient(45deg, var(--accent), #f4d03f);

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            font-weight: 600;

            font-size: 24px;

            margin: 0 auto 10px;

            border: 3px solid rgba(255, 255, 255, 0.2);

        }



        .user-info-sidebar h3 {

            font-size: 16px;

            font-weight: 600;

            margin-bottom: 5px;

        }



        .user-info-sidebar p {

            font-size: 13px;

            color: rgba(255, 255, 255, 0.8);

        }



        .menu {

            margin-top: 30px;

        }



        .menu-item {

            padding: 14px 15px;

            margin-bottom: 8px;

            border-radius: 10px;

            cursor: pointer;

            display: flex;

            align-items: center;

            transition: all 0.3s ease;

            position: relative;

        }



        .menu-item:hover, .menu-item.active {

            background-color: rgba(255, 255, 255, 0.15);

            transform: translateX(5px);

        }



        .menu-item i {

            width: 30px;

            font-size: 18px;

            margin-right: 12px;

        }



        .menu-item span {

            font-size: 15px;

            font-weight: 500;

        }



        .menu-item .badge {

            position: absolute;

            right: 10px;

            background-color: var(--danger);

            color: white;

            border-radius: 50%;

            width: 20px;

            height: 20px;

            font-size: 11px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: 600;

        }



        /* Main Content Styles */

        .main-content {

            grid-column: 2;

            padding: 30px;

            margin-left: 280px;

        }



        .header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 30px;

            background-color: var(--card-bg);

            padding: 25px;

            border-radius: 15px;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);

        }



        .header-title h2 {

            font-size: 28px;

            font-weight: 600;

            color: var(--primary-dark);

            margin-bottom: 5px;

        }



        .header-title p {

            color: #6c757d;

            font-size: 16px;

        }



        .header-actions {

            display: flex;

            gap: 15px;

            align-items: center;

        }



        .btn {

            padding: 10px 20px;

            border: none;

            border-radius: 8px;

            cursor: pointer;

            font-weight: 500;

            text-decoration: none;

            display: inline-flex;

            align-items: center;

            gap: 8px;

            transition: all 0.3s ease;

        }



        .btn-primary {

            background-color: var(--primary);

            color: white;

        }



        .btn-primary:hover {

            background-color: var(--primary-dark);

            transform: translateY(-2px);

        }



        .btn-outline {

            border: 2px solid var(--primary);

            color: var(--primary);

            background-color: transparent;

        }



        .btn-outline:hover {

            background-color: var(--primary);

            color: white;

        }



        /* Tab Navigation */

        .tab-navigation {

            display: flex;

            background-color: var(--card-bg);

            border-radius: 12px;

            padding: 5px;

            margin-bottom: 30px;

            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);

            overflow-x: auto;

        }



        .tab-item {

            padding: 12px 20px;

            border-radius: 8px;

            cursor: pointer;

            transition: all 0.3s ease;

            white-space: nowrap;

            display: flex;

            align-items: center;

            gap: 8px;

            font-weight: 500;

            color: #6c757d;

        }



        .tab-item:hover {

            background-color: var(--primary-light);

            color: var(--primary);

        }



        .tab-item.active {

            background-color: var(--primary);

            color: white;

        }



        /* Content Sections */

        .content-section {

            display: none;

        }



        .content-section.active {

            display: block;

        }



        /* Cards Container */

        .cards-container {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));

            gap: 25px;

            margin-bottom: 30px;

        }



        .card {

            background-color: var(--card-bg);

            border-radius: 15px;

            padding: 25px;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);

            transition: all 0.3s ease;

        }



        .card:hover {

            transform: translateY(-5px);

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);

        }



        .card-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

        }



        .card-header h4 {

            font-size: 18px;

            font-weight: 600;

            color: var(--primary-dark);

        }



        .card-header i {

            color: var(--accent);

            font-size: 24px;

        }



        /* Stats Cards */

        .stats-grid {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));

            gap: 20px;

            margin-bottom: 30px;

        }



        .stat-card {

            background: linear-gradient(135deg, var(--primary), var(--primary-dark));

            color: white;

            padding: 25px;

            border-radius: 15px;

            display: flex;

            align-items: center;

            justify-content: space-between;

        }



        .stat-content h3 {

            font-size: 32px;

            font-weight: 700;

            margin-bottom: 5px;

        }



        .stat-content p {

            font-size: 14px;

            opacity: 0.9;

        }



        .stat-icon {

            font-size: 40px;

            opacity: 0.7;

        }



        /* Status Badge */

        .status-badge {

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

            text-transform: uppercase;

        }



        .status-soumis { background-color: #e3f2fd; color: #1976d2; }

        .status-conforme { background-color: #e8f5e8; color: #2e7d32; }

        .status-non-conforme { background-color: #ffebee; color: #c62828; }

        .status-en-attente { background-color: #fff3e0; color: #ef6c00; }



        /* Timeline */

        .timeline {

            position: relative;

            padding-left: 30px;

        }



        .timeline:before {

            content: '';

            position: absolute;

            left: 15px;

            top: 0;

            bottom: 0;

            width: 2px;

            background-color: var(--border-color);

        }



        .timeline-item {

            position: relative;

            margin-bottom: 25px;

        }



        .timeline-item:before {

            content: '';

            position: absolute;

            left: -22px;

            top: 5px;

            width: 12px;

            height: 12px;

            border-radius: 50%;

            background-color: var(--primary);

            border: 3px solid white;

            box-shadow: 0 0 0 2px var(--primary);

        }



        .timeline-content {

            background-color: #f8f9fa;

            padding: 15px;

            border-radius: 8px;

            border-left: 4px solid var(--primary);

        }



        .timeline-content h5 {

            color: var(--primary-dark);

            font-weight: 600;

            margin-bottom: 5px;

        }



        .timeline-content p {

            color: #6c757d;

            font-size: 14px;

            margin-bottom: 5px;

        }



        .timeline-date {

            font-size: 12px;

            color: var(--accent);

            font-weight: 500;

        }



        /* Notification Styles */

        .notification-item {

            padding: 15px;

            border-bottom: 1px solid var(--border-color);

            transition: all 0.3s ease;

        }



        .notification-item:hover {

            background-color: var(--primary-light);

        }



        .notification-item.urgent {

            border-left: 4px solid var(--danger);

            background-color: rgba(220, 53, 69, 0.05);

        }



        .notification-item.non-lue {

            background-color: rgba(42, 92, 170, 0.05);

            border-left: 4px solid var(--primary);

        }



        /* Documents List */

        .document-item {

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 15px;

            border: 1px solid var(--border-color);

            border-radius: 8px;

            margin-bottom: 10px;

            transition: all 0.3s ease;

        }



        .document-item:hover {

            border-color: var(--primary);

            background-color: var(--primary-light);

        }



        .document-info {

            display: flex;

            align-items: center;

            gap: 15px;

        }



        .document-icon {

            width: 40px;

            height: 40px;

            background-color: var(--primary-light);

            border-radius: 8px;

            display: flex;

            align-items: center;

            justify-content: center;

            color: var(--primary);

        }



        /* Form Styles */

        .form-group {

            margin-bottom: 20px;

        }



        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-weight: 500;

            color: var(--text-dark);

        }



        .form-control {

            width: 100%;

            padding: 12px;

            border: 2px solid var(--border-color);

            border-radius: 8px;

            font-size: 14px;

            transition: all 0.3s ease;

        }



        .form-control:focus {

            outline: none;

            border-color: var(--primary);

            box-shadow: 0 0 0 3px rgba(42, 92, 170, 0.1);

        }



        textarea.form-control {

            resize: vertical;

            min-height: 100px;

        }



        /* Progress Bar */

        .progress {

            width: 100%;

            height: 8px;

            background-color: #e9ecef;

            border-radius: 4px;

            overflow: hidden;

            margin-bottom: 10px;

        }



        .progress-bar {

            height: 100%;

            background: linear-gradient(90deg, var(--primary), var(--accent));

            transition: width 0.3s ease;

        }



        /* Alert Styles */

        .alert {

            padding: 15px 20px;

            border-radius: 8px;

            margin-bottom: 20px;

            display: flex;

            align-items: center;

            gap: 10px;

        }



        .alert-success {

            background-color: #d4edda;

            border-left: 4px solid var(--success);

            color: #155724;

        }



        .alert-warning {

            background-color: #fff3cd;

            border-left: 4px solid var(--warning);

            color: #856404;

        }



        .alert-danger {

            background-color: #f8d7da;

            border-left: 4px solid var(--danger);

            color: #721c24;

        }



        .alert-info {

            background-color: #cce7f0;

            border-left: 4px solid var(--info);

            color: #0c5460;

        }



        /* Responsive Design */

        @media (max-width: 1200px) {

            .cards-container {

                grid-template-columns: 1fr;

            }

        }



        @media (max-width: 768px) {

            .dashboard-container {

                grid-template-columns: 1fr;

            }



            .sidebar {

                width: 100%;

                height: auto;

                position: relative;

            }



            .main-content {

                margin-left: 0;

                padding: 20px;

            }



            .header {

                flex-direction: column;

                gap: 15px;

                text-align: center;

            }



            .tab-navigation {

                flex-direction: column;

            }



            .stats-grid {

                grid-template-columns: 1fr;

            }

        }



        /* Loading Spinner */

        .spinner {

            border: 4px solid #f3f3f3;

            border-top: 4px solid var(--primary);

            border-radius: 50%;

            width: 40px;

            height: 40px;

            animation: spin 1s linear infinite;

            margin: 20px auto;

        }



        @keyframes spin {

            0% { transform: rotate(0deg); }

            100% { transform: rotate(360deg); }

        }



        .text-center {

            text-align: center;

        }



        .mt-20 {

            margin-top: 20px;

        }



        .mb-20 {

            margin-bottom: 20px;

        }

    </style>

</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <h1>GestionMySoutenance</h1>
        </div>

        <div class="user-profile-sidebar">
            <div class="avatar-sidebar">
                <?php if ($etudiant->photo_profil_chemin): ?>
                    <img src="<?= htmlspecialchars($etudiant->photo_profil_chemin) ?>" alt="Photo de profil" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <?= substr($etudiant->prenom, 0, 1) . substr($etudiant->nom, 0, 1) ?>
                <?php endif; ?>
            </div>
            <div class="user-info-sidebar">
                <h3><?= htmlspecialchars($etudiant->prenom . ' ' . $etudiant->nom) ?></h3>
                <p><?= htmlspecialchars($etudiant->niveau_etude . ' - ' . $etudiant->filiere) ?></p>
            </div>
        </div>

        <div class="menu">
            <div class="menu-item <?= $ongletActif === 'dashboard' ? 'active' : '' ?>" onclick="showTab('dashboard')">
                <i class="fas fa-home"></i>
                <span>Tableau de Bord</span>
            </div>
            <div class="menu-item <?= $ongletActif === 'profil' ? 'active' : '' ?>" onclick="showTab('profil')">
                <i class="fas fa-user"></i>
                <span>Mon Profil</span>
            </div>
            <div class="menu-item <?= $ongletActif === 'rapport' ? 'active' : '' ?>" onclick="showTab('rapport')">
                <i class="fas fa-file-alt"></i>
                <span>Mon Rapport</span>
                <?php if ($rapportEtudiant && $rapportEtudiant->statut === 'CORRECTIONS_REQUISES'): ?>
                    <span class="badge">!</span>
                <?php endif; ?>
            </div>
            <div class="menu-item <?= $ongletActif === 'documents' ? 'active' : '' ?>" onclick="showTab('documents')">
                <i class="fas fa-file-download"></i>
                <span>Mes Documents</span>
                <?php if (count($documentsOfficials) > 0): ?>
                    <span class="badge"><?= count($documentsOfficials) ?></span>
                <?php endif; ?>
            </div>
            <div class="menu-item <?= $ongletActif === 'reclamations' ? 'active' : '' ?>" onclick="showTab('reclamations')">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Mes Réclamations</span>
                <?php if ($reclamationsEnCours > 0): ?>
                    <span class="badge"><?= $reclamationsEnCours ?></span>
                <?php endif; ?>
            </div>
            <div class="menu-item <?= $ongletActif === 'ressources' ? 'active' : '' ?>" onclick="showTab('ressources')">
                <i class="fas fa-question-circle"></i>
                <span>Ressources & Aide</span>
            </div>
            <div class="menu-item" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-title">
                <h2>Mon Espace MySoutenance</h2>
                <p>Bienvenue dans votre espace personnel de gestion de soutenance</p>
            </div>
            <div class="header-actions">
                <?php if ($notificationsNonLues > 0): ?>
                    <button class="btn btn-outline" onclick="showTab('dashboard'); scrollToNotifications();">
                        <i class="fas fa-bell"></i>
                        <?= $notificationsNonLues ?> notifications
                    </button>
                <?php endif; ?>
                <a href="<?= $base_url ?>/Aide/index" class="btn btn-primary">
                    <i class="fas fa-question-circle"></i>
                    Aide
                </a>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <div class="tab-item <?= $ongletActif === 'dashboard' ? 'active' : '' ?>" onclick="showTab('dashboard')">
                <i class="fas fa-home"></i>
                <span>Tableau de Bord</span>
            </div>
            <div class="tab-item <?= $ongletActif === 'profil' ? 'active' : '' ?>" onclick="showTab('profil')">
                <i class="fas fa-user"></i>
                <span>Mon Profil</span>
            </div>
            <div class="tab-item <?= $ongletActif === 'rapport' ? 'active' : '' ?>" onclick="showTab('rapport')">
                <i class="fas fa-file-alt"></i>
                <span>Mon Rapport</span>
            </div>
            <div class="tab-item <?= $ongletActif === 'documents' ? 'active' : '' ?>" onclick="showTab('documents')">
                <i class="fas fa-file-download"></i>
                <span>Mes Documents</span>
            </div>
            <div class="tab-item <?= $ongletActif === 'reclamations' ? 'active' : '' ?>" onclick="showTab('reclamations')">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Réclamations</span>
            </div>
            <div class="tab-item <?= $ongletActif === 'ressources' ? 'active' : '' ?>" onclick="showTab('ressources')">
                <i class="fas fa-question-circle"></i>
                <span>Ressources</span>
            </div>
        </div>

        <!-- TABLEAU DE BORD -->
        <div id="dashboard" class="content-section <?= $ongletActif === 'dashboard' ? 'active' : '' ?>">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?= $totalNotifications ?></h3>
                        <p>Notifications</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?= $alertesUrgentes ?></h3>
                        <p>Alertes urgentes</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?= $reclamationsEnCours ?></h3>
                        <p>Réclamations en cours</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?= count($prochainesEcheances) ?></h3>
                        <p>Échéances à venir</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>

            <!-- Current Status and Quick Actions -->
            <div class="cards-container">
                <!-- Statut du Rapport -->
                <div class="card">
                    <div class="card-header">
                        <h4>Statut de mon rapport</h4>
                        <i class="fas fa-file-alt"></i>
                    </div>

                    <?php if ($rapportEtudiant): ?>
                        <h3><?= htmlspecialchars($rapportEtudiant->titre) ?></h3>
                        <p>Soumis le: <?= date('d/m/Y', strtotime($rapportEtudiant->date_soumission)) ?></p>

                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Progression</span>
                                <span>75%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 75%"></div>
                            </div>
                        </div>

                        <div class="statut-label statut-<?= strtolower(str_replace('_', '-', $rapportEtudiant->statut)) ?>">
                            <?= $rapportEtudiant->statut === 'EN_REVISION' ? 'En révision' : $rapportEtudiant->statut ?>
                        </div>

                        <div class="action-buttons">
                            <button class="action-btn primary" onclick="showRapportDetails()">
                                <i class="fas fa-eye"></i> Voir les détails
                            </button>
                            <button class="action-btn secondary" onclick="downloadRapport()">
                                <i class="fas fa-download"></i> Télécharger
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h3>Aucun rapport soumis</h3>
                            <p>Vous n'avez pas encore soumis de rapport de stage</p>
                            <button class="action-btn primary" onclick="showTab('rapport')">
                                <i class="fas fa-plus"></i> Soumettre un rapport
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Soutenance -->
                <div class="card">
                    <div class="card-header">
                        <h4>Ma soutenance</h4>
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>

                    <?php if ($soutenance): ?>
                        <h3>Date de soutenance</h3>
                        <p><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y à H:i', strtotime($soutenance->date)) ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($soutenance->salle) ?></p>

                        <h4 class="mt-20">Membres du jury</h4>
                        <ul class="jury-list">
                            <?php foreach ($soutenance->jury as $membre): ?>
                                <li><?= htmlspecialchars($membre) ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="action-buttons">
                            <button class="action-btn secondary">
                                <i class="fas fa-info-circle"></i> Plus d'informations
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>Aucune date de soutenance</h3>
                            <p>Votre date de soutenance n'a pas encore été fixée</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notifications et Alertes -->
            <div class="cards-container">
                <!-- Notifications récentes -->
                <div class="card" id="notifications-section">
                    <div class="card-header">
                        <h4>Notifications récentes</h4>
                        <i class="fas fa-bell"></i>
                    </div>

                    <?php if (count($notifications) > 0): ?>
                        <div class="timeline">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="timeline-item">
                                    <div class="timeline-content <?= !$notification->lue ? 'non-lue' : '' ?>">
                                        <h5><?= htmlspecialchars($notification->titre) ?></h5>
                                        <p><?= htmlspecialchars($notification->contenu) ?></p>
                                        <div class="timeline-date">
                                            <i class="fas fa-clock"></i> <?= date('d/m/Y', strtotime($notification->date)) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-center mt-20">
                            <button class="action-btn secondary" onclick="showAllNotifications()">
                                <i class="fas fa-list"></i> Voir toutes les notifications
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <h3>Aucune notification</h3>
                            <p>Vous n'avez aucune notification pour le moment</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Alertes et échéances -->
                <div class="card">
                    <div class="card-header">
                        <h4>Alertes et échéances</h4>
                        <i class="fas fa-exclamation-circle"></i>
                    </div>

                    <?php if (count($alertes) > 0 || count($prochainesEcheances) > 0): ?>
                        <h5>Alertes importantes</h5>
                        <?php foreach ($alertes as $alerte): ?>
                            <div class="alert <?= $alerte->priorite === 'HAUTE' ? 'alert-danger' : 'alert-warning' ?>">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong><?= htmlspecialchars($alerte->titre) ?></strong>
                                    <p><?= htmlspecialchars($alerte->contenu) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <h5 class="mt-20">Échéances à venir</h5>
                        <ul class="timeline">
                            <?php foreach ($prochainesEcheances as $echeance): ?>
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <h5><?= htmlspecialchars($echeance->titre) ?></h5>
                                        <div class="timeline-date">
                                            <i class="fas fa-calendar-day"></i> <?= date('d/m/Y', strtotime($echeance->date)) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h3>Aucune alerte</h3>
                            <p>Vous n'avez aucune alerte ou échéance imminente</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- MON PROFIL -->
        <div id="profil" class="content-section">
            <div class="card">
                <div class="card-header">
                    <h4>Mes informations personnelles</h4>
                    <i class="fas fa-user-edit"></i>
                </div>

                <form id="profilForm" method="POST" action="<?= $base_url ?>/Etudiant/updateProfil" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($etudiant->prenom) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($etudiant->nom) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($etudiant->email) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($etudiant->telephone) ?>">
                    </div>

                    <div class="form-group">
                        <label for="filiere">Filière</label>
                        <input type="text" id="filiere" name="filiere" class="form-control" value="<?= htmlspecialchars($etudiant->filiere) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="niveau_etude">Niveau d'études</label>
                        <input type="text" id="niveau_etude" name="niveau_etude" class="form-control" value="<?= htmlspecialchars($etudiant->niveau_etude) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="photo">Photo de profil</label>
                        <input type="file" id="photo" name="photo" class="form-control" onchange="previewPhoto(this)">
                        <div id="photoPreview" class="mt-10" style="width: 100px; height: 100px; border-radius: 50%; background-color: #f0f0f0; margin-top: 10px; overflow: hidden;">
                            <?php if ($etudiant->photo_profil_chemin): ?>
                                <img src="<?= htmlspecialchars($etudiant->photo_profil_chemin) ?>" alt="Photo actuelle" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: 24px; color: #666;">
                                    <?= substr($etudiant->prenom, 0, 1) . substr($etudiant->nom, 0, 1) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="action-btn primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <button type="reset" class="action-btn secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MON RAPPORT -->
        <div id="rapport" class="content-section">
            <?php if ($rapportEtudiant): ?>
                <!-- Rapport existant -->
                <div class="card">
                    <div class="card-header">
                        <h4>Mon rapport de stage</h4>
                        <i class="fas fa-file-pdf"></i>
                    </div>

                    <h3><?= htmlspecialchars($rapportEtudiant->titre) ?></h3>
                    <p>Soumis le: <?= date('d/m/Y', strtotime($rapportEtudiant->date_soumission)) ?></p>

                    <div class="statut-label statut-<?= strtolower(str_replace('_', '-', $rapportEtudiant->statut)) ?>">
                        <?= $rapportEtudiant->statut === 'EN_REVISION' ? 'En révision' : $rapportEtudiant->statut ?>
                    </div>

                    <div class="action-buttons">
                        <button class="action-btn primary" onclick="downloadRapport()">
                            <i class="fas fa-download"></i> Télécharger
                        </button>
                        <button class="action-btn secondary" onclick="showRapportDetails()">
                            <i class="fas fa-eye"></i> Voir les détails
                        </button>
                        <button class="action-btn warning" onclick="showUpdateForm()">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                    </div>

                    <?php if ($rapportEtudiant->statut === 'CORRECTIONS_REQUISES'): ?>
                        <div class="alert alert-warning mt-20">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Corrections requises</strong>
                                <p>Votre rapport nécessite des corrections avant validation finale</p>
                            </div>
                            <button class="action-btn warning" onclick="showCorrectionForm()">
                                <i class="fas fa-tasks"></i> Voir les corrections
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Historique des versions -->
                <div class="card">
                    <div class="card-header">
                        <h4>Historique des statuts</h4>
                        <i class="fas fa-history"></i>
                    </div>

                    <div class="timeline">
                        <?php foreach ($historiqueStatuts as $statut): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <h5><?= $statut->statut === 'SOUMIS' ? 'Soumission' : ($statut->statut === 'EN_REVISION' ? 'En révision' : $statut->statut) ?></h5>
                                    <p><?= htmlspecialchars($statut->commentaire) ?></p>
                                    <div class="timeline-date">
                                        <i class="fas fa-clock"></i> <?= date('d/m/Y', strtotime($statut->date)) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Formulaire de soumission initial -->
                <div class="card">
                    <div class="card-header">
                        <h4>Soumettre mon rapport</h4>
                        <i class="fas fa-file-upload"></i>
                    </div>

                    <form id="rapportForm" method="POST" action="<?= $base_url ?>/Rapport/submit" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="titre">Titre du rapport</label>
                            <input type="text" id="titre" name="titre" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="fichier">Fichier du rapport (PDF uniquement)</label>
                            <input type="file" id="fichier" name="fichier" class="form-control" accept=".pdf" required>
                        </div>

                        <div class="form-group">
                            <label for="commentaire">Commentaire (optionnel)</label>
                            <textarea id="commentaire" name="commentaire" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="action-btn primary">
                                <i class="fas fa-paper-plane"></i> Soumettre
                            </button>
                            <button type="button" class="action-btn secondary" onclick="saveDraft()">
                                <i class="fas fa-save"></i> Sauvegarder en brouillon
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- MES DOCUMENTS -->
        <div id="documents" class="content-section">
            <div class="card">
                <div class="card-header">
                    <h4>Mes documents officiels</h4>
                    <i class="fas fa-folder-open"></i>
                </div>

                <?php if (count($documentsOfficials) > 0): ?>
                    <?php foreach ($documentsOfficials as $document): ?>
                        <div class="document-item">
                            <div class="document-info">
                                <div class="document-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div>
                                    <h5><?= htmlspecialchars($document->nom) ?></h5>
                                    <p>Ajouté le: <?= date('d/m/Y', strtotime($document->date_ajout)) ?></p>
                                </div>
                            </div>
                            <div class="document-actions">
                                <button class="document-btn" onclick="previewDocument('/documents/<?= $document->nom ?>.pdf')">
                                    <i class="fas fa-eye"></i> Voir
                                </button>
                                <button class="document-btn" onclick="downloadDocument('/documents/<?= $document->nom ?>.pdf')">
                                    <i class="fas fa-download"></i> Télécharger
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>Aucun document disponible</h3>
                        <p>Vous n'avez aucun document officiel dans votre espace</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- MES RÉCLAMATIONS -->
        <div id="reclamations" class="content-section">
            <!-- Formulaire nouvelle réclamation -->
            <div class="card">
                <div class="card-header">
                    <h4>Nouvelle réclamation</h4>
                    <i class="fas fa-plus-circle"></i>
                </div>

                <form id="reclamationForm" method="POST" action="<?= $base_url ?>/Reclamation/submit" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="sujet">Sujet</label>
                        <input type="text" id="sujet" name="sujet" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="piece_jointe">Pièce jointe (optionnel)</label>
                        <input type="file" id="piece_jointe" name="piece_jointe" class="form-control">
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="action-btn primary">
                            <i class="fas fa-paper-plane"></i> Envoyer
                        </button>
                        <button type="reset" class="action-btn secondary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste des réclamations -->
            <div class="card">
                <div class="card-header">
                    <h4>Mes réclamations</h4>
                    <i class="fas fa-list"></i>
                </div>

                <?php if (count($reclamations) > 0): ?>
                    <div class="timeline">
                        <?php foreach ($reclamations as $reclamation): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <h5><?= htmlspecialchars($reclamation->sujet) ?></h5>
                                    <div class="timeline-date">
                                        <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($reclamation->date)) ?>
                                        <span class="statut-label <?= $reclamation->statut === 'EN_COURS' ? 'statut-en-revision' : 'statut-conforme' ?>">
                                            <?= $reclamation->statut === 'EN_COURS' ? 'En cours' : 'Traitée' ?>
                                        </span>
                                    </div>
                                    <button class="action-btn secondary" onclick="showReclamationDetails(<?= $reclamation->id ?>)">
                                        <i class="fas fa-info-circle"></i> Voir les détails
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Aucune réclamation</h3>
                        <p>Vous n'avez effectué aucune réclamation</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RESSOURCES & AIDE -->
        <div id="ressources" class="content-section">
            <div class="cards-container">
                <!-- Guides et documents utiles -->
                <div class="card">
                    <div class="card-header">
                        <h4>Guides et ressources</h4>
                        <i class="fas fa-book"></i>
                    </div>

                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <h5>Guide de rédaction du rapport</h5>
                                <p>Document PDF</p>
                            </div>
                        </div>
                        <button class="document-btn" onclick="previewDocument('/guides/guide_redaction.pdf')">
                            <i class="fas fa-eye"></i> Voir
                        </button>
                    </div>

                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <h5>Modèle de rapport</h5>
                                <p>Document Word</p>
                            </div>
                        </div>
                        <button class="document-btn" onclick="downloadDocument('/modeles/modele_rapport.docx')">
                            <i class="fas fa-download"></i> Télécharger
                        </button>
                    </div>

                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <h5>Grille d'évaluation</h5>
                                <p>Document PDF</p>
                            </div>
                        </div>
                        <button class="document-btn" onclick="previewDocument('/guides/grille_evaluation.pdf')">
                            <i class="fas fa-eye"></i> Voir
                        </button>
                    </div>
                </div>

                <!-- Support et contacts -->
                <div class="card">
                    <div class="card-header">
                        <h4>Support et contacts</h4>
                        <i class="fas fa-headset"></i>
                    </div>

                    <h5>Contactez le support</h5>
                    <p>Pour toute question ou problème technique, contactez notre équipe de support.</p>

                    <div class="alert alert-info">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email</strong>
                            <p>support@gestionsoutenance.fr</p>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Téléphone</strong>
                            <p>01 23 45 67 89 (9h-17h)</p>
                        </div>
                    </div>

                    <h5 class="mt-20">Tuteurs pédagogiques</h5>
                    <p>Pour les questions concernant votre stage ou votre rapport.</p>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h5>M. Martin Dupont</h5>
                                <p>Tuteur informatique</p>
                                <p>martin.dupont@universite.fr</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h5>Mme Sophie Leroy</h5>
                                <p>Tutrice génie logiciel</p>
                                <p>sophie.leroy@universite.fr</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ intégrée -->
            <div class="card" id="faq-section">
                <div class="card-header">
                    <h4>Foire aux questions</h4>
                    <i class="fas fa-question-circle"></i>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h5>Quand dois-je soumettre mon rapport final ?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>La date limite de soumission du rapport final est indiquée dans votre calendrier académique. Vous pouvez la consulter dans la section "Échéances" de votre tableau de bord.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h5>Comment modifier mon rapport après soumission ?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>Si votre rapport est encore en cours d'évaluation, vous pouvez le modifier en accédant à la section "Mon Rapport" et en cliquant sur le bouton "Modifier". Après validation par votre tuteur, les modifications ne sont plus possibles.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h5>Comment connaître la date exacte de ma soutenance ?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>La date de votre soutenance sera communiquée par votre tuteur pédagogique au moins 3 semaines à l'avance. Vous recevrez une notification et la date apparaîtra dans votre tableau de bord.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les détails -->
<div id="detailModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalBody"></div>
    </div>
</div>

<script>
    // Variables globales
    let currentTab = '<?= $ongletActif ?>';
    const baseUrl = '<?= $base_url ?>';

    // Initialiser l'onglet actif au chargement
    window.onload = function() {
        showTab(currentTab);
    };

    // Gestion des onglets
    function showTab(tabName) {
        // Masquer toutes les sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Afficher la section active
        document.getElementById(tabName).classList.add('active');

        // Mettre à jour l'URL avec le paramètre tab
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);

        // Mettre à jour le menu latéral et la barre d'onglets
        document.querySelectorAll('.menu-item, .tab-item').forEach(item => {
            item.classList.remove('active');
        });

        // Activer l'élément du menu latéral correspondant
        document.querySelectorAll('.menu-item').forEach(item => {
            if (item.getAttribute('onclick') === `showTab('${tabName}')`) {
                item.classList.add('active');
            }
        });

        // Activer l'élément de la barre d'onglets correspondant
        document.querySelectorAll('.tab-item').forEach(item => {
            if (item.getAttribute('onclick') === `showTab('${tabName}')`) {
                item.classList.add('active');
            }
        });

        // Faire défiler vers le haut
        window.scrollTo(0, 0);
    }

    // Fonctions utilitaires
    function scrollToNotifications() {
        const element = document.getElementById('notifications-section');
        element.scrollIntoView({ behavior: 'smooth' });
    }

    function showAllNotifications() {
        // Logique pour afficher toutes les notifications
        window.location.href = baseUrl + '/Notification/index';
    }

    function showRapportDetails() {
        // Logique pour afficher les détails du rapport
        window.location.href = baseUrl + '/Rapport/details';
    }

    function showCorrectionForm() {
        // Logique pour afficher le formulaire de correction
        window.location.href = baseUrl + '/Rapport/corrections';
    }

    function showUpdateForm() {
        // Logique pour modifier le rapport
        window.location.href = baseUrl + '/Rapport/edit';
    }

    function downloadRapport() {
        // Logique pour télécharger le rapport
        window.location.href = baseUrl + '/Rapport/download';
    }

    function downloadDocument(url) {
        // Logique pour télécharger un document
        window.location.href = baseUrl + '/Document/download?file=' + encodeURIComponent(url);
    }

    function previewDocument(url) {
        window.open(baseUrl + '/Document/preview?file=' + encodeURIComponent(url), '_blank');
    }

    function showReclamationDetails(id) {
        // Logique pour afficher les détails d'une réclamation
        window.location.href = baseUrl + '/Reclamation/details/' + id;
    }

    function showFAQ() {
        const faqSection = document.getElementById('faq-section');
        faqSection.style.display = 'block';
        faqSection.scrollIntoView({ behavior: 'smooth' });
    }

    function toggleFAQ(element) {
        const content = element.nextElementSibling;
        const icon = element.querySelector('i');

        if (content.style.display === 'block') {
            content.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        } else {
            content.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    }

    // Prévisualisation de la photo de profil
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            const preview = document.getElementById('photoPreview');

            reader.onload = function(e) {
                preview.innerHTML = '';
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                preview.appendChild(img);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Réinitialiser le formulaire
    function resetForm() {
        document.getElementById('profilForm').reset();
        const preview = document.getElementById('photoPreview');

        <?php if ($etudiant->photo_profil_chemin): ?>
        preview.innerHTML = '<img src="<?= htmlspecialchars($etudiant->photo_profil_chemin) ?>" alt="Photo actuelle" style="width: 100%; height: 100%; object-fit: cover;">';
        <?php else: ?>
        preview.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: 24px; color: #666;"><?= substr($etudiant->prenom, 0, 1) . substr($etudiant->nom, 0, 1) ?></div>';
        <?php endif; ?>
    }

    // Sauvegarder en brouillon
    function saveDraft() {
        // Logique pour sauvegarder en brouillon
        alert('Brouillon sauvegardé avec succès');
        // Envoyer les données via AJAX dans une implémentation réelle
    }

    // Déconnexion
    function logout() {
        if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
            window.location.href = baseUrl + '/Auth/logout';
        }
    }

    // Modal functions
    function showModal(content) {
        document.getElementById('modalBody').innerHTML = content;
        document.getElementById('detailModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    // Validation des formulaires
    document.addEventListener('DOMContentLoaded', function() {
        // Validation du formulaire de profil
        const profilForm = document.getElementById('profilForm');
        if (profilForm) {
            profilForm.addEventListener('submit', function(e) {
                // Validation simple pour démonstration
                const email = document.getElementById('email').value;
                if (!email.includes('@')) {
                    e.preventDefault();
                    alert('Veuillez entrer une adresse email valide');
                }
            });
        }

        // Validation du formulaire de rapport
        const rapportForm = document.getElementById('rapportForm');
        if (rapportForm) {
            rapportForm.addEventListener('submit', function(e) {
                const fichier = document.getElementById('fichier').value;
                if (!fichier) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un fichier à soumettre');
                } else if (!fichier.toLowerCase().endsWith('.pdf')) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un fichier PDF');
                }
            });
        }

        // Validation du formulaire de réclamation
        const reclamationForm = document.getElementById('reclamationForm');
        if (reclamationForm) {
            reclamationForm.addEventListener('submit', function(e) {
                const sujet = document.getElementById('sujet').value;
                const description = document.getElementById('description').value;

                if (!sujet || !description) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs obligatoires');
                }
            });
        }

        // Initialiser les FAQ comme fermées
        const faqContents = document.querySelectorAll('.faq-content');
        faqContents.forEach(content => {
            content.style.display = 'none';
        });
    });

    // Fermer la modal en cliquant en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>
</body>
</html>