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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace MySoutenance - <?= htmlspecialchars($etudiant->prenom . ' ' . $etudiant->nom) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
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
                <a href="/aide" class="btn btn-primary">
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
                        <p>Alertes Urgentes</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?= count($documentsOfficials) ?></h3>
                        <p>Documents Disponibles</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-file-download"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?= $reclamationsEnCours ?></h3>
                        <p>Réclamations en Cours</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <!-- Current Status and Quick Actions -->
            <div class="cards-container">
                <!-- Statut du Rapport -->
                <div class="card">
                    <div class="card-header">
                        <h4>Statut de votre rapport</h4>
                        <i class="fas fa-file-contract"></i>
                    </div>

                    <?php if ($rapportEtudiant): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Statut actuel :</strong>
                                <span class="status-badge status-<?= strtolower(str_replace('_', '-', $statutRapport->libelle ?? 'en-attente')) ?>">
                                    <?= htmlspecialchars($statutRapport->libelle ?? 'En attente') ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($statutRapport && $statutRapport->description): ?>
                            <p class="mb-20"><?= htmlspecialchars($statutRapport->description) ?></p>
                        <?php endif; ?>

                        <!-- Informations du rapport -->
                        <div class="timeline-content">
                            <h5><?= htmlspecialchars($rapportEtudiant->libelle_rapport_etudiant) ?></h5>
                            <p><strong>Thème :</strong> <?= htmlspecialchars($rapportEtudiant->theme) ?></p>
                            <p><strong>Date de soumission :</strong> <?= date('d/m/Y H:i', strtotime($rapportEtudiant->date_soumission)) ?></p>
                            <?php if ($rapportEtudiant->nombre_pages): ?>
                                <p><strong>Nombre de pages :</strong> <?= $rapportEtudiant->nombre_pages ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Actions rapides -->
                        <div class="mt-20">
                            <?php if ($statutRapport && $statutRapport->libelle === 'CORRECTIONS_REQUISES'): ?>
                                <a href="#" class="btn btn-primary" onclick="showTab('rapport')">
                                    <i class="fas fa-edit"></i>
                                    Soumettre les corrections
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-outline" onclick="showRapportDetails()">
                                <i class="fas fa-eye"></i>
                                Voir détails
                            </button>
                        </div>

                        <!-- Historique des statuts -->
                        <?php if (count($historiqueStatuts) > 0): ?>
                            <h5 style="margin-top: 25px; margin-bottom: 15px; color: var(--primary-dark);">Historique des statuts</h5>
                            <div class="timeline">
                                <?php foreach ($historiqueStatuts as $historique): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-content">
                                            <h5><?= htmlspecialchars($historique->libelle) ?></h5>
                                            <p><?= htmlspecialchars($historique->description) ?></p>
                                            <div class="timeline-date"><?= date('d M Y H:i', strtotime($historique->date)) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Aucun rapport soumis</strong><br>
                                Vous n'avez pas encore soumis votre rapport de stage.
                            </div>
                        </div>
                        <a href="#" class="btn btn-primary" onclick="showTab('rapport')">
                            <i class="fas fa-upload"></i>
                            Soumettre mon rapport
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Soutenance -->
                <div class="card">
                    <div class="card-header">
                        <h4>Ma soutenance</h4>
                        <i class="fas fa-calendar-alt"></i>
                    </div>

                    <?php if ($soutenance): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Soutenance programmée</strong><br>
                                Le <?= date('d/m/Y à H:i', strtotime($soutenance->date)) ?>
                            </div>
                        </div>

                        <div class="timeline-content">
                            <p><strong>Lieu :</strong> <?= htmlspecialchars($soutenance->lieu ?? 'À définir') ?></p>
                            <p><strong>Durée :</strong> <?= htmlspecialchars($soutenance->duree ?? '30 minutes') ?></p>
                            <?php if ($soutenance->jury): ?>
                                <p><strong>Jury :</strong> <?= htmlspecialchars($soutenance->jury) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="mt-20">
                            <button class="btn btn-outline">
                                <i class="fas fa-download"></i>
                                Télécharger la convocation
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Soutenance non programmée</strong><br>
                                Votre soutenance sera programmée après validation de votre rapport.
                            </div>
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

                    <div class="notifications-list" style="max-height: 400px; overflow-y: auto;">
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                                <div class="notification-item <?= $notification->urgent ? 'urgent' : '' ?> <?= !$notification->lue ? 'non-lue' : '' ?>">
                                    <h5><?= htmlspecialchars($notification->titre) ?></h5>
                                    <p><?= htmlspecialchars($notification->contenu) ?></p>
                                    <div class="notification-date">
                                        <?= date('d/m/Y H:i', strtotime($notification->date)) ?>
                                        <?php if ($notification->action_requise): ?>
                                            <span class="badge badge-danger">Action requise</span>
                                        <?php endif; ?>
                                        <?php if (!$notification->lue): ?>
                                            <span class="badge" style="background-color: var(--primary);">Nouveau</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (count($notifications) > 5): ?>
                                <div class="text-center mt-20">
                                    <button class="btn btn-outline" onclick="showAllNotifications()">
                                        Voir toutes les notifications (<?= count($notifications) ?>)
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-center">Aucune notification</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Alertes et échéances -->
                <div class="card">
                    <div class="card-header">
                        <h4>Alertes et échéances</h4>
                        <i class="fas fa-exclamation-circle"></i>
                    </div>

                    <?php if (count($alertes) > 0): ?>
                        <?php foreach ($alertes as $alerte): ?>
                            <div class="alert alert-<?= $alerte->priorite === 'URGENTE' ? 'danger' : 'warning' ?>">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong><?= htmlspecialchars($alerte->titre) ?></strong><br>
                                    <?= htmlspecialchars($alerte->description) ?><br>
                                    <small>Échéance: <?= date('d/m/Y', strtotime($alerte->date_echeance)) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <div>Aucune alerte en ce moment</div>
                        </div>
                    <?php endif; ?>

                    <!-- Prochaines échéances -->
                    <?php if (count($prochainesEcheances) > 0): ?>
                        <h5 style="margin-top: 25px; margin-bottom: 15px;">Prochaines échéances</h5>
                        <div class="timeline">
                            <?php foreach ($prochainesEcheances as $echeance): ?>
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <h5><?= htmlspecialchars($echeance['titre']) ?></h5>
                                        <p><?= htmlspecialchars($echeance['description']) ?></p>
                                        <div class="timeline-date"><?= date('d M Y', strtotime($echeance['date'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

                <form id="profilForm" method="POST" action="/etudiant/update-profil" enctype="multipart/form-data">
                    <div class="cards-container">
                        <div>
                            <!-- Photo de profil -->
                            <div class="form-group text-center">
                                <label>Photo de profil</label>
                                <div class="avatar-sidebar" style="margin: 0 auto 15px;">
                                    <?php if ($etudiant->photo_profil_chemin): ?>
                                        <img id="photoPreview" src="<?= htmlspecialchars($etudiant->photo_profil_chemin) ?>" alt="Photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <span id="initialesPreview"><?= substr($etudiant->prenom, 0, 1) . substr($etudiant->nom, 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewPhoto(this)">
                                <small class="text-muted">Formats acceptés: JPG, PNG, GIF (max 2MB)</small>
                            </div>

                            <!-- Informations non modifiables -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <div>Ces informations sont synchronisées depuis le système de l'établissement et ne peuvent pas être modifiées ici.</div>
                            </div>

                            <div class="form-group">
                                <label>Numéro étudiant</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($etudiant->numero_carte_etudiant) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($etudiant->nom) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($etudiant->prenom) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label>Filière</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($etudiant->filiere) ?>" readonly>
                            </div>
                        </div>

                        <div>
                            <!-- Informations modifiables -->
                            <h5 style="margin-bottom: 20px; color: var(--primary-dark);">Informations de contact</h5>

                            <div class="form-group">
                                <label for="email">Adresse e-mail</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($etudiant->email) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="telephone">Téléphone</label>
                                <input type="tel" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($etudiant->telephone) ?>">
                            </div>

                            <div class="form-group">
                                <label for="adresse">Adresse postale</label>
                                <textarea id="adresse" name="adresse" class="form-control"><?= htmlspecialchars($etudiant->adresse_postale ?? '') ?></textarea>
                            </div>

                            <!-- Changement de mot de passe -->
                            <h5 style="margin: 30px 0 20px; color: var(--primary-dark);">Sécurité</h5>

                            <div class="form-group">
                                <label for="ancien_mot_de_passe">Ancien mot de passe</label>
                                <input type="password" id="ancien_mot_de_passe" name="ancien_mot_de_passe" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
                                <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" class="form-control">
                                <small class="text-muted">Laissez vide si vous ne souhaitez pas changer votre mot de passe</small>
                            </div>

                            <div class="form-group">
                                <label for="confirmer_mot_de_passe">Confirmer le nouveau mot de passe</label>
                                <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" class="form-control">
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Sauvegarder les modifications
                                </button>
                                <button type="button" class="btn btn-outline" onclick="resetForm()">
                                    <i class="fas fa-undo"></i>
                                    Annuler
                                </button>
                            </div>
                        </div>
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
                        <i class="fas fa-file-alt"></i>
                    </div>

                    <!-- Statut actuel -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Statut :</strong>
                            <span class="status-badge status-<?= strtolower(str_replace('_', '-', $statutRapport->libelle ?? 'en-attente')) ?>">
                                <?= htmlspecialchars($statutRapport->libelle ?? 'En attente') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Détails du rapport -->
                    <div class="cards-container">
                        <div>
                            <h5>Informations du rapport</h5>
                            <div class="form-group">
                                <label>Titre du rapport</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($rapportEtudiant->libelle_rapport_etudiant) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Thème</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($rapportEtudiant->theme) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Résumé</label>
                                <textarea class="form-control" readonly style="min-height: 120px;"><?= htmlspecialchars($rapportEtudiant->resume) ?></textarea>
                            </div>
                        </div>
                        <div>
                            <h5>Informations administratives</h5>
                            <div class="form-group">
                                <label>Numéro d'attestation de stage</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($rapportEtudiant->numero_attestation_stage) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Date de soumission</label>
                                <input type="text" class="form-control" value="<?= date('d/m/Y H:i', strtotime($rapportEtudiant->date_soumission)) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Nombre de pages</label>
                                <input type="text" class="form-control" value="<?= $rapportEtudiant->nombre_pages ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Actions possibles -->
                    <div class="mt-20">
                        <?php if ($statutRapport && $statutRapport->libelle === 'CORRECTIONS_REQUISES'): ?>
                            <button class="btn btn-primary" onclick="showCorrectionForm()">
                                <i class="fas fa-edit"></i>
                                Soumettre les corrections
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-outline" onclick="downloadRapport()">
                            <i class="fas fa-download"></i>
                            Télécharger le rapport
                        </button>

                        <?php if ($statutRapport && in_array($statutRapport->libelle, ['SOUMIS', 'EN_VERIFICATION_CONFORMITE'])): ?>
                            <button class="btn btn-outline" onclick="showUpdateForm()">
                                <i class="fas fa-edit"></i>
                                Modifier le rapport
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Commentaires et feedback -->
                    <?php if ($statutRapport && $statutRapport->commentaires): ?>
                        <div class="alert alert-warning" style="margin-top: 20px;">
                            <h5><i class="fas fa-comments"></i> Commentaires des évaluateurs</h5>
                            <p><?= nl2br(htmlspecialchars($statutRapport->commentaires)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Formulaire de soumission initial -->
                <div class="card">
                    <div class="card-header">
                        <h4>Soumettre mon rapport de stage</h4>
                        <i class="fas fa-upload"></i>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Première soumission</strong><br>
                            Veuillez remplir tous les champs requis et télécharger votre rapport de stage.
                        </div>
                    </div>

                    <form id="rapportForm" method="POST" action="/etudiant/submit-rapport" enctype="multipart/form-data">
                        <div class="cards-container">
                            <div>
                                <h5>Informations du rapport</h5>
                                <div class="form-group">
                                    <label for="titre_rapport">Titre du rapport *</label>
                                    <input type="text" id="titre_rapport" name="titre_rapport" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="theme">Thème *</label>
                                    <input type="text" id="theme" name="theme" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="resume">Résumé *</label>
                                    <textarea id="resume" name="resume" class="form-control" required style="min-height: 150px;" placeholder="Résumé de votre rapport de stage (maximum 500 mots)"></textarea>
                                    <small class="text-muted">Maximum 500 mots</small>
                                </div>
                            </div>

                            <div>
                                <h5>Informations administratives</h5>
                                <div class="form-group">
                                    <label for="numero_attestation">Numéro d'attestation de stage *</label>
                                    <input type="text" id="numero_attestation" name="numero_attestation" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="fichier_rapport">Fichier du rapport *</label>
                                    <input type="file" id="fichier_rapport" name="fichier_rapport" class="form-control" accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">Formats acceptés: PDF, DOC, DOCX (max 10MB)</small>
                                </div>

                                <div class="form-group">
                                    <label for="pieces_jointes">Pièces jointes (optionnel)</label>
                                    <input type="file" id="pieces_jointes" name="pieces_jointes[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png" multiple>
                                    <small class="text-muted">Annexes, attestations, etc.</small>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="confirmer_version_definitive" required>
                                        Je confirme que cette version est définitive et conforme aux règles de l'établissement
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-20">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i>
                                Soumettre le rapport
                            </button>
                            <button type="button" class="btn btn-outline" onclick="saveDraft()">
                                <i class="fas fa-save"></i>
                                Sauvegarder en brouillon
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Historique des versions -->
            <?php if ($rapportEtudiant): ?>
                <div class="card">
                    <div class="card-header">
                        <h4>Historique des versions</h4>
                        <i class="fas fa-history"></i>
                    </div>

                    <div class="timeline">
                        <?php foreach ($historiqueStatuts as $version): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <h5>Version <?= $version->version ?? '1' ?> - <?= htmlspecialchars($version->libelle) ?></h5>
                                    <p><?= htmlspecialchars($version->description) ?></p>
                                    <div class="timeline-date">
                                        <?= date('d M Y H:i', strtotime($version->date)) ?>
                                        <?php if ($version->fichier): ?>
                                            <a href="<?= $version->fichier ?>" class="btn btn-outline" style="margin-left: 10px; padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-download"></i>
                                                Télécharger
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- MES DOCUMENTS -->
        <div id="documents" class="content-section">
            <div class="card">
                <div class="card-header">
                    <h4>Mes documents officiels</h4>
                    <i class="fas fa-file-download"></i>
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
                                    <p>Type: <?= htmlspecialchars($document->type) ?></p>
                                    <small class="text-muted">
                                        Généré le <?= date('d/m/Y H:i', strtotime($document->date_generation)) ?>
                                        <?php if ($document->version > 1): ?>
                                            - Version <?= $document->version ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div>
                                <a href="<?= $document->lien ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-download"></i>
                                    Télécharger
                                </a>
                                <button class="btn btn-outline" onclick="previewDocument('<?= $document->lien ?>')">
                                    <i class="fas fa-eye"></i>
                                    Aperçu
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Aucun document disponible</strong><br>
                            Vos documents officiels apparaîtront ici une fois générés par l'administration.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Types de documents disponibles -->
                <div class="mt-20">
                    <h5>Documents que vous pouvez obtenir :</h5>
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <li>Attestation de dépôt de rapport</li>
                        <li>Bulletin de notes final</li>
                        <li>Attestation de fréquentation</li>
                        <li>Procès-verbal de soutenance</li>
                        <li>Relevé de notes officiel</li>
                    </ul>
                </div>
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

                <form id="reclamationForm" method="POST" action="/etudiant/submit-reclamation" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="sujet_reclamation">Sujet de la réclamation *</label>
                        <select id="sujet_reclamation" name="sujet_reclamation" class="form-control" required>
                            <option value="">Sélectionnez un sujet</option>
                            <option value="erreur_note">Erreur dans les notes</option>
                            <option value="erreur_pv">Erreur dans le procès-verbal</option>
                            <option value="probleme_technique">Problème technique</option>
                            <option value="delai_traitement">Délai de traitement trop long</option>
                            <option value="information_incorrecte">Information incorrecte</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description_reclamation">Description détaillée *</label>
                        <textarea id="description_reclamation" name="description_reclamation" class="form-control" required style="min-height: 150px;" placeholder="Décrivez précisément votre problème..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="justificatifs">Pièces justificatives (optionnel)</label>
                        <input type="file" id="justificatifs" name="justificatifs[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png" multiple>
                        <small class="text-muted">Ajoutez des documents qui appuient votre réclamation</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Soumettre la réclamation
                    </button>
                </form>
            </div>

            <!-- Liste des réclamations -->
            <div class="card">
                <div class="card-header">
                    <h4>Mes réclamations</h4>
                    <i class="fas fa-list"></i>
                </div>

                <?php if (count($reclamations) > 0): ?>
                    <?php foreach ($reclamations as $reclamation): ?>
                        <div class="document-item" style="margin-bottom: 15px;">
                            <div class="document-info">
                                <div class="document-icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div>
                                    <h5>
                                        <?= htmlspecialchars($reclamation->sujet_reclamation) ?>
                                        <span class="status-badge status-<?= strtolower(str_replace('_', '-', $reclamation->statut)) ?>">
                                            <?= htmlspecialchars($reclamation->statut) ?>
                                        </span>
                                    </h5>
                                    <p><?= htmlspecialchars(substr($reclamation->description_reclamation, 0, 100)) ?>...</p>
                                    <small class="text-muted">
                                        Soumise le <?= date('d/m/Y H:i', strtotime($reclamation->date_soumission)) ?>
                                        - Numéro: #<?= $reclamation->id_reclamation ?>
                                    </small>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-outline" onclick="showReclamationDetails(<?= $reclamation->id_reclamation ?>)">
                                    <i class="fas fa-eye"></i>
                                    Détails
                                </button>
                            </div>
                        </div>

                        <?php if ($reclamation->reponse_reclamation): ?>
                            <div class="alert alert-success" style="margin-left: 70px; margin-bottom: 15px;">
                                <h6><i class="fas fa-reply"></i> Réponse (<?= date('d/m/Y H:i', strtotime($reclamation->date_reponse)) ?>)</h6>
                                <p><?= nl2br(htmlspecialchars($reclamation->reponse_reclamation)) ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Aucune réclamation</strong><br>
                            Vous n'avez soumis aucune réclamation pour le moment.
                        </div>
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
                        <h4>Guides et documents utiles</h4>
                        <i class="fas fa-book"></i>
                    </div>

                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <h5>Guide de rédaction des rapports</h5>
                                <p>Document officiel avec toutes les consignes</p>
                            </div>
                        </div>
                        <div>
                            <a href="/documents/guide-redaction.pdf" class="btn btn-primary" target="_blank">
                                <i class="fas fa-download"></i>
                                Télécharger
                            </a>
                        </div>
                    </div>

                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-file-word"></i>
                            </div>
                            <div>
                                <h5>Modèle de page de garde</h5>
                                <p>Template officiel pour la page de garde</p>
                            </div>
                        </div>
                        <div>
                            <a href="/documents/modele-page-garde.docx" class="btn btn-primary">
                                <i class="fas fa-download"></i>
                                Télécharger
                            </a>
                        </div>
                    </div>

                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <h5>Critères d'évaluation</h5>
                                <p>Grille d'évaluation des rapports de stage</p>
                            </div>
                        </div>
                        <div>
                            <a href="/documents/criteres-evaluation.pdf" class="btn btn-primary" target="_blank">
                                <i class="fas fa-eye"></i>
                                Consulter
                            </a>
                        </div>
                    </div>

                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div>
                                <h5>Foire aux questions (FAQ)</h5>
                                <p>Réponses aux questions les plus fréquentes</p>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="showFAQ()">
                                <i class="fas fa-eye"></i>
                                Consulter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Support et contacts -->
                <div class="card">
                    <div class="card-header">
                        <h4>Support et aide</h4>
                        <i class="fas fa-headset"></i>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Besoin d'aide ?</strong><br>
                            Notre équipe est là pour vous accompagner.
                        </div>
                    </div>

                    <!-- Contacts utiles -->
                    <h5>Contacts utiles</h5>
                    <div class="timeline-content" style="margin-bottom: 20px;">
                        <p><strong>Service de conformité :</strong></p>
                        <p>📧 conformite@universite.edu</p>
                        <p>📞 +225 XX XX XX XX</p>
                        <p>🕒 Lundi - Vendredi : 8h - 17h</p>
                    </div>

                    <div class="timeline-content" style="margin-bottom: 20px;">
                        <p><strong>Support technique :</strong></p>
                        <p>📧 support.technique@universite.edu</p>
                        <p>📞 +225 XX XX XX XX</p>
                        <p>🕒 7j/7 : 8h - 20h</p>
                    </div>

                    <!-- Formulaire de contact support -->
                    <h5>Contactez le support technique</h5>
                    <form id="supportForm" method="POST" action="/etudiant/contact-support">
                        <div class="form-group">
                            <label for="type_probleme">Type de problème</label>
                            <select id="type_probleme" name="type_probleme" class="form-control" required>
                                <option value="">Sélectionnez le type</option>
                                <option value="connexion">Problème de connexion</option>
                                <option value="upload">Problème de téléchargement</option>
                                <option value="affichage">Problème d'affichage</option>
                                <option value="fonctionnalite">Fonctionnalité défaillante</option>
                                <option value="autre">Autre problème technique</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description_probleme">Description du problème</label>
                            <textarea id="description_probleme" name="description_probleme" class="form-control" required style="min-height: 100px;" placeholder="Décrivez votre problème en détail..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Contacter le support
                        </button>
                    </form>
                </div>
            </div>

            <!-- FAQ intégrée -->
            <div class="card" id="faq-section" style="display: none;">
                <div class="card-header">
                    <h4>Foire aux questions</h4>
                    <i class="fas fa-question-circle"></i>
                </div>

                <div class="faq-item">
                    <h5 onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right"></i>
                        Comment soumettre mon rapport de stage ?
                    </h5>
                    <div class="faq-content">
                        <p>Pour soumettre votre rapport, rendez-vous dans l'onglet "Mon Rapport", remplissez tous les champs requis et téléchargez votre fichier au format PDF, DOC ou DOCX.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <h5 onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right"></i>
                        Que faire si mon rapport est marqué comme non conforme ?
                    </h5>
                    <div class="faq-content">
                        <p>Si votre rapport est non conforme, consultez les commentaires des évaluateurs dans l'onglet "Mon Rapport", effectuez les corrections demandées et resoumettez votre rapport.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <h5 onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right"></i>
                        Comment télécharger mes documents officiels ?
                    </h5>
                    <div class="faq-content">
                        <p>Vos documents officiels sont disponibles dans l'onglet "Mes Documents". Cliquez sur "Télécharger" à côté du document souhaité.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <h5 onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right"></i>
                        Comment suivre l'état de ma réclamation ?
                    </h5>
                    <div class="faq-content">
                        <p>Dans l'onglet "Mes Réclamations", vous pouvez voir le statut de toutes vos réclamations et les réponses éventuelles.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les détails -->
<div id="detailModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 id="modalTitle">Détails</h4>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Contenu dynamique -->
        </div>
    </div>
</div>

<script>
    // Variables globales
    let currentTab = '<?= $ongletActif ?>';

    // Gestion des onglets
    function showTab(tabName) {
        // Cacher toutes les sections
        const sections = document.querySelectorAll('.content-section');
        sections.forEach(section => {
            section.classList.remove('active');
        });

        // Désactiver tous les onglets
        const tabItems = document.querySelectorAll('.tab-item, .menu-item');
        tabItems.forEach(item => {
            item.classList.remove('active');
        });

        // Activer la section et l'onglet sélectionnés
        const targetSection = document.getElementById(tabName);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        // Activer les onglets correspondants
        const targetTabs = document.querySelectorAll(`[onclick="showTab('${tabName}')"]`);
        targetTabs.forEach(tab => {
            tab.classList.add('active');
        });

        currentTab = tabName;

        // Mettre à jour l'URL sans recharger la page
        history.pushState(null, null, `?tab=${tabName}`);
    }

    // Fonctions utilitaires
    function scrollToNotifications() {
        const notificationSection = document.getElementById('notifications-section');
        if (notificationSection) {
            notificationSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function showAllNotifications() {
        // Logique pour afficher toutes les notifications
        alert('Affichage de toutes les notifications (à implémenter)');
    }

    function showRapportDetails() {
        // Logique pour afficher les détails du rapport
        alert('Détails du rapport (à implémenter)');
    }

    function showCorrectionForm() {
        // Logique pour afficher le formulaire de correction
        alert('Formulaire de correction (à implémenter)');
    }

    function showUpdateForm() {
        // Logique pour modifier le rapport
        alert('Formulaire de modification (à implémenter)');
    }

    function downloadRapport() {
        // Logique pour télécharger le rapport
        alert('Téléchargement du rapport (à implémenter)');
    }

    function previewDocument(url) {
        window.open(url, '_blank');
    }

    function showReclamationDetails(id) {
        // Logique pour afficher les détails d'une réclamation
        alert(`Détails de la réclamation #${id} (à implémenter)`);
    }

    function showFAQ() {
        const faqSection = document.getElementById('faq-section');
        if (faqSection.style.display === 'none') {
            faqSection.style.display = 'block';
            faqSection.scrollIntoView({ behavior: 'smooth' });
        } else {
            faqSection.style.display = 'none';
        }
    }

    function toggleFAQ(element) {
        const content = element.nextElementSibling;
        const icon = element.querySelector('i');

        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            icon.className = 'fas fa-chevron-down';
        } else {
            content.style.display = 'none';
            icon.className = 'fas fa-chevron-right';
        }
    }

    // Prévisualisation de la photo de profil
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                const initiales = document.getElementById('initialesPreview');

                if (preview) {
                    preview.src = e.target.result;
                } else {
                    // Créer l'élément img s'il n'existe pas
                    const avatarContainer = document.querySelector('.avatar-sidebar');
                    avatarContainer.innerHTML = `<img id="photoPreview" src="${e.target.result}" alt="Photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                }

                if (initiales) {
                    initiales.style.display = 'none';
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Réinitialiser le formulaire
    function resetForm() {
        if (confirm('Êtes-vous sûr de vouloir annuler vos modifications ?')) {
            location.reload();
        }
    }

    // Sauvegarder en brouillon
    function saveDraft() {
        // Logique pour sauvegarder en brouillon
        alert('Brouillon sauvegardé (à implémenter)');
    }

    // Déconnexion
    function logout() {
        if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
            window.location.href = '/logout.php';
        }
    }

    // Modal functions
    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    // Validation des formulaires
    document.addEventListener('DOMContentLoaded', function() {
        // Validation du formulaire de profil
        const profilForm = document.getElementById('profilForm');
        if (profilForm) {
            profilForm.addEventListener('submit', function(e) {
                const nouveauMotDePasse = document.getElementById('nouveau_mot_de_passe').value;
                const confirmerMotDePasse = document.getElementById('confirmer_mot_de_passe').value;

                if (nouveauMotDePasse && nouveauMotDePasse !== confirmerMotDePasse) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                    return false;
                }
            });
        }

        // Validation du formulaire de rapport
        const rapportForm = document.getElementById('rapportForm');
        if (rapportForm) {
            rapportForm.addEventListener('submit', function(e) {
                const resume = document.getElementById('resume').value;
                const words = resume.trim().split(/\s+/).length;

                if (words > 500) {
                    e.preventDefault();
                    alert('Le résumé ne doit pas dépasser 500 mots.');
                    return false;
                }
            });
        }

        // Initialiser les FAQ comme fermées
        const faqContents = document.querySelectorAll('.faq-content');
        faqContents.forEach(content => {
            content.style.display = 'none';
        });
    });

    // Gestion des notifications en temps réel (WebSocket ou polling)
    function checkNewNotifications() {
        // À implémenter : vérification des nouvelles notifications
        // fetch('/api/notifications/check')
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.newNotifications > 0) {
        //             // Mettre à jour l'interface
        //         }
        //     });
    }

    // Vérifier les notifications toutes les 30 secondes
    setInterval(checkNewNotifications, 30000);

    // Styles pour la modal et FAQ
    const additionalStyles = `
<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 20px;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #999;
}

.close:hover {
    color: var(--danger);
}

.faq-item {
    border-bottom: 1px solid var(--border-color);
    padding: 15px 0;
}

.faq-item h5 {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary-dark);
    margin-bottom: 10px;
    transition: color 0.3s ease;
}

.faq-item h5:hover {
    color: var(--primary);
}

.faq-content {
    padding-left: 25px;
    color: var(--text-dark);
}

.status-soumis { background-color: #e3f2fd; color: #1976d2; }
.status-conforme { background-color: #e8f5e8; color: #2e7d32; }
.status-non-conforme { background-color: #ffebee; color: #c62828; }
.status-en-attente { background-color: #fff3e0; color: #ef6c00; }
.status-en-cours { background-color: #e3f2fd; color: #1976d2; }
.status-traitee { background-color: #e8f5e8; color: #2e7d32; }
.status-rejetee { background-color: #ffebee; color: #c62828; }
</style>
`;

    // Ajouter les styles supplémentaires
    document.head.insertAdjacentHTML('beforeend', additionalStyles);
</script>

</body>
</html>