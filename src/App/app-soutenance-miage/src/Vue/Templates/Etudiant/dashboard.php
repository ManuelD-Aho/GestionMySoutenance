<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation Étudiant - Dashboard</title>
    <style>
        /* Variables globales */
        :root {
            --primary-color: #1e40af;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --dark-color: #34495e;
            --light-color: #ecf0f1;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        /* Navbar styles */
        .navbar {
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            height: 60px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            z-index: 100;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .navbar-brand-icon {
            width: 36px;
            height: 36px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: var(--primary-color);
            font-weight: bold;
            font-size: 18px;
        }

        .navbar-menu {
            display: flex;
            list-style-type: none;
        }

        .navbar-item {
            height: 60px;
            display: flex;
            align-items: center;
            position: relative;
            padding: 0 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .navbar-item:hover, .navbar-item.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .navbar-item.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: white;
        }

        .navbar-user {
            display: flex;
            align-items: center;
        }

        .navbar-notification {
            margin-right: 20px;
            position: relative;
            cursor: pointer;
        }

        .notification-icon {
            font-size: 20px;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 20px;
            transition: background-color 0.2s;
        }

        .user-info:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-name {
            margin-right: 10px;
        }

        .user-icon {
            width: 35px;
            height: 35px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: bold;
        }

        /* Main content container */
        .container {
            display: flex;
            flex-grow: 1;
            min-height: calc(100vh - 60px);
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: white;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            z-index: 90;
        }

        .sidebar.collapsed {
            width: 65px;
        }

        .header {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .logo {
            font-weight: bold;
            font-size: 16px;
            flex-grow: 1;
            color: #333;
        }

        .toggle-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .toggle-btn:hover {
            background-color: #f0f0f0;
        }

        .menu {
            flex-grow: 1;
            padding: 10px 0;
            overflow-y: auto;
        }

        .menu-category {
            padding: 10px 15px;
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar.collapsed .menu-category {
            display: none;
        }

        .menu-item {
            padding: 12px 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            border-radius: 6px;
            margin: 2px 8px;
            position: relative;
        }

        .menu-item:hover {
            background-color: #f0f7ff;
        }

        .menu-item.active {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 500;
        }

        .menu-item-icon {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #666;
        }

        .menu-item.active .menu-item-icon {
            background-color: #0369a1;
            color: white;
        }

        .sidebar.collapsed .menu-item-text {
            display: none;
        }

        .menu-item-arrow {
            margin-left: auto;
            transition: transform 0.3s;
        }

        .menu-item[aria-expanded="true"] .menu-item-arrow {
            transform: rotate(90deg);
        }

        .sidebar.collapsed .menu-item-arrow {
            display: none;
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            margin-left: 8px;
            margin-right: 8px;
        }

        .submenu.open {
            max-height: 500px;
        }

        .sidebar.collapsed .submenu.open {
            max-height: 0;
            overflow: hidden;
        }

        .submenu-item {
            padding: 10px 12px 10px 40px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            border-radius: 6px;
            margin: 4px 0;
        }

        .submenu-item:hover {
            background-color: #f0f7ff;
        }

        .submenu-item.active {
            color: #0369a1;
            font-weight: 500;
        }

        .submenu-checkbox {
            margin-right: 8px;
            accent-color: #0369a1;
        }

        /* Badge pour nouvelles fonctionnalités */
        .badge-new {
            background-color: #10b981;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 10px;
            text-transform: uppercase;
        }

        /* Main content styles */
        .main-content {
            flex-grow: 1;
            padding: 25px;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn-outline {
            border: 1px solid #ddd;
            background-color: white;
            color: #666;
        }

        .btn-outline:hover {
            border-color: #bbb;
            background-color: #f8f8f8;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #1e3a8a;
        }

        .btn-success {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }

        .btn-success:hover {
            background-color: #27ae60;
        }

        .btn-danger {
            background-color: var(--accent-color);
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-action {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        /* Styles du système d'évaluation */
        .form-card {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            gap: 15px;
            align-items: center;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group-small {
            flex: 0 0 100px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--dark-color);
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: var(--transition);
        }

        input:focus, select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.2);
            outline: none;
        }

        .ue-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .ue-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            align-items: center;
        }

        .btn-container {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
            gap: 10px;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
        }

        thead {
            background-color: var(--dark-color);
            color: white;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        tbody tr {
            border-bottom: 1px solid #ddd;
            transition: var(--transition);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .hidden {
            display: none;
        }

        .status-indicator {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-success {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }

        .status-warning {
            background-color: rgba(241, 196, 15, 0.15);
            color: #f39c12;
        }

        .status-danger {
            background-color: rgba(231, 76, 60, 0.15);
            color: #c0392b;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .moyenne-container {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            display: flex;
            gap: 15px;
        }

        .moyenne-item {
            padding: 8px 15px;
            border-radius: 5px;
            background-color: white;
            box-shadow: var(--shadow);
        }

        .moyenne-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-right: 5px;
        }
        .table-buttons-container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .table-container {
            flex: 1;
            overflow-x: auto;
        }

        #historiqueTable {
            width: 100%;
            border-collapse: collapse;
        }

        #historiqueTable th,
        #historiqueTable td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        .button-panel {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .button-panel button {
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
        }

        /* Styles des modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-close {
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
            transition: var(--transition);
        }

        .modal-close:hover {
            color: #333;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        /* Styles responsifs */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-container {
                flex-direction: column;
            }

            .moyenne-container {
                flex-direction: column;
            }

            .sidebar {
                width: 60px;
            }

            .sidebar .menu-item-text,
            .sidebar .menu-category,
            .sidebar .logo {
                display: none;
            }

            .sidebar.collapsed {
                width: 0;
                overflow: hidden;
            }

            .main-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<!-- Navbar horizontale en haut -->
<div class="navbar">
    <div class="navbar-brand">
        <div class="navbar-brand-icon">E</div>
        <span>EduGestion</span>
    </div>
    <ul class="navbar-menu">
        <li class="navbar-item">Tableau de bord</li>
        <li class="navbar-item active">Étudiants</li>
        <li class="navbar-item">Cours</li>
        <li class="navbar-item">Rapports</li>
    </ul>
    <div class="navbar-user">
        <div class="navbar-notification">
            <div class="notification-icon">🔔</div>
            <div class="notification-badge">2</div>
        </div>
        <div class="user-info">
            <span class="user-name">Professeur</span>
            <div class="user-icon">P</div>
        </div>
    </div>
</div>

<!-- Container principal qui contient le sidebar et le contenu -->
<div class="container">
    <div class="sidebar" id="sidebar">
        <div class="header">
            <div class="logo" id="logo">Navigation</div>
            <button class="toggle-btn" id="toggle-btn">≡</button>
        </div>
        <div class="menu">
            <div class="menu-category">Principal</div>
            <div class="menu-item" data-submenu="menu1" aria-expanded="false">
                <div class="menu-item-icon">📊</div>
                <div class="menu-item-text">Tableau de bord</div>
                <div class="menu-item-arrow">❯</div>
            </div>
            <div class="submenu" id="menu1">
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-overview">
                    <label for="checkbox-overview">Vue d'ensemble</label>
                </div>
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-analytics">
                    <label for="checkbox-analytics">Analytiques</label>
                </div>
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-performance">
                    <label for="checkbox-performance">Performance</label>
                    <span class="badge-new">New</span>
                </div>
            </div>

            <div class="menu-item active" data-submenu="menu2" aria-expanded="true">
                <div class="menu-item-icon">👨‍🎓</div>
                <div class="menu-item-text">Étudiants</div>
                <div class="menu-item-arrow">❯</div>
            </div>
            <div class="submenu open" id="menu2">
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-liste">
                    <label for="checkbox-liste">Liste</label>
                </div>
                <div class="submenu-item active">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-evaluation" checked>
                    <label for="checkbox-evaluation">Évaluation</label>
                </div>
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-resultats">
                    <label for="checkbox-resultats">Résultats</label>
                </div>
            </div>

            <div class="menu-category">Académique</div>
            <div class="menu-item" data-submenu="menu3" aria-expanded="false">
                <div class="menu-item-icon">📚</div>
                <div class="menu-item-text">Cours</div>
                <div class="menu-item-arrow">❯</div>
            </div>
            <div class="submenu" id="menu3">
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-listeCours">
                    <label for="checkbox-listeCours">Liste des cours</label>
                </div>
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-planning">
                    <label for="checkbox-planning">Planning</label>
                </div>
            </div>

            <div class="menu-item" data-submenu="menu4" aria-expanded="false">
                <div class="menu-item-icon">📈</div>
                <div class="menu-item-text">Rapports</div>
                <div class="menu-item-arrow">❯</div>
            </div>
            <div class="submenu" id="menu4">
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-semester">
                    <label for="checkbox-semester">Semestre</label>
                </div>
                <div class="submenu-item">
                    <input type="checkbox" class="submenu-checkbox" id="checkbox-annuel">
                    <label for="checkbox-annuel">Annuel</label>
                </div>
            </div>

            <div class="menu-category">Configuration</div>
            <div class="menu-item">
                <div class="menu-item-icon">⚙️</div>
                <div class="menu-item-text">Paramètres</div>
            </div>
            <div class="menu-item">
                <div class="menu-item-icon">👥</div>
                <div class="menu-item-text">Utilisateurs</div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">Évaluation Étudiant</div>
            <div class="action-buttons">
                <div class="form-group">
                    <label for="annee">Année Académique</label>
                    <input type="text" id="annee" placeholder="2024-2025">
                </div>
            </div>
        </div>

        <!-- Contenu du système d'évaluation -->
        <div class="form-card">
            <h2>Informations Générales</h2>

            <div class="form-row">

                <div class="form-group">
                    <label for="numEtudiant">Numéro Étudiant</label>
                    <input type="text" id="numEtudiant" placeholder="Ex: ET12345">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" placeholder="Nom de l'étudiant">
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" placeholder="Prénom de l'étudiant">
                </div>
                <div class="form-group">
                    <label for="promotion">Promotion</label>
                    <input type="text" id="promotion" placeholder="Ex: Master 2 Informatique">
                </div>
            </div>

            <h2>Unités d'Enseignement</h2>

            <div id="ue-container">
                <div class="ue-section" data-ue-id="1">
                    <div class="ue-header">
                        <h3>UE 1</h3>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ue1">Intitulé</label>
                            <input type="text" id="ue1" placeholder="Nom de la matière">
                        </div>
                        <div class="form-group-small">
                            <label for="note1">Note</label>
                            <input type="number" id="note1" min="0" max="20" step="0.5" placeholder="0-20">
                        </div>
                        <div class="form-group-small">
                            <label for="credit1">Crédit</label>
                            <input type="number" id="credit1" min="1" max="30" placeholder="ECTS">
                        </div>
                    </div>
                </div>

                <div class="ue-section" data-ue-id="2">
                    <div class="ue-header">
                        <h3>UE 2</h3>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ue2">Intitulé</label>
                            <input type="text" id="ue2" placeholder="Nom de la matière">
                        </div>
                        <div class="form-group-small">
                            <label for="note2">Note</label>
                            <input type="number" id="note2" min="0" max="20" step="0.5" placeholder="0-20">
                        </div>
                        <div class="form-group-small">
                            <label for="credit2">Crédit</label>
                            <input type="number" id="credit2" min="1" max="30" placeholder="ECTS">
                        </div>
                    </div>
                </div>

                <div class="ue-section" data-ue-id="3">
                    <div class="ue-header">
                        <h3>UE 3</h3>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ue3">Intitulé</label>
                            <input type="text" id="ue3" placeholder="Nom de la matière">
                        </div>
                        <div class="form-group-small">
                            <label for="note3">Note</label>
                            <input type="number" id="note3" min="0" max="20" step="0.5" placeholder="0-20">
                        </div>
                        <div class="form-group-small">
                            <label for="credit3">Crédit</label>
                            <input type="number" id="credit3" min="1" max="30" placeholder="ECTS">
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-container">
                <button id="addUeBtn" class="btn btn-primary">Ajouter une UE</button>
            </div>

            <div class="form-footer">
                <div class="moyenne-container">
                    <div class="moyenne-item">
                        <span class="moyenne-label">Moyenne:</span>
                        <span id="moyenneValue">-</span>/20
                    </div>
                    <div class="moyenne-item">
                        <span class="moyenne-label">Total Crédits:</span>
                        <span id="creditTotal">0</span> ECTS
                    </div>
                    <div class="moyenne-item">
                        <span class="moyenne-label">Statut:</span>
                        <span id="statusValue" class="status-indicator">Non évalué</span>
                    </div>
                </div>

                <button id="submitBtn" class="btn btn-success">
                    Ajouter à l'historique
                </button>
            </div>
        </div>

        <h2>Historique des Évaluations</h2>
        <div class="table-buttons-container">
            <div class="table-container">
                <table id="historiqueTable">
                    <thead>
                    <tr>
                        <th>N° Étudiant</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Promotion</th>
                        <th>Moyenne</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>2021001</td>
                        <td>Koné</td>
                        <td>Amara</td>
                        <td>MIAGE 2023</td>
                        <td>14.5</td>
                        <td>Admis</td>
                        <td><button>Voir</button></td>
                    </tr>
                    <tr>
                        <td>2021002</td>
                        <td>Traoré</td>
                        <td>Fatou</td>
                        <td>MIAGE 2023</td>
                        <td>12.7</td>
                        <td>Admis</td>
                        <td><button>Voir</button></td>
                    </tr>
                    <tr>
                        <td>2021003</td>
                        <td>Ouattara</td>
                        <td>Issa</td>
                        <td>MIAGE 2023</td>
                        <td>9.8</td>
                        <td>Ajourné</td>
                        <td><button>Voir</button></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="button-panel">
                <button>Ajouter un étudiant</button>
                <button>Exporter PDF</button>
                <button>Filtrer par statut</button>
                <button>Réinitialiser</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let counter = 4; // Pour l'ajout de nouvelles UEs
    let editingRowIndex = -1; // Pour suivre la ligne en cours d'édition
    const students = []; // Pour stocker les données des étudiants

    // Fonction d'initialisation
    document.addEventListener('DOMContentLoaded', function() {
        initEventListeners();
        updateAverageAndCredit();
    });

    // Initialisation des écouteurs d'événements
    function initEventListeners() {
        // Bouton d'ajout d'UE
        document.getElementById('addUeBtn').addEventListener('click', addNewUE);

        // Bouton de soumission
        document.getElementById('submitBtn').addEventListener('click', ajouterEtudiant);

        // Écouteurs pour les champs de notes et crédits
        document.querySelectorAll('input[id^="note"], input[id^="credit"]').forEach(input => {
            input.addEventListener('input', updateAverageAndCredit);
        });

        // Fermeture des modales
        document.querySelectorAll('.modal-close, .modal-close-btn').forEach(btn => {
            btn.addEventListener('click', closeModals);
        });

        // Bouton de sauvegarde des modifications
        document.getElementById('saveEditBtn').addEventListener('click', saveEdit);
    }

    // Ajouter une nouvelle UE
    function addNewUE() {
        const ueContainer = document.getElementById('ue-container');
        const newUeSection = document.createElement('div');
        newUeSection.className = 'ue-section';
        newUeSection.dataset.ueId = counter;

        newUeSection.innerHTML = `
      <div class="ue-header">
        <h3>UE ${counter}</h3>
        <button class="btn btn-danger btn-action remove-ue">Supprimer</button>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="ue${counter}">Intitulé</label>
          <input type="text" id="ue${counter}" placeholder="Nom de la matière">
        </div>
        <div class="form-group-small">
          <label for="note${counter}">Note</label>
          <input type="number" id="note${counter}" min="0" max="20" step="0.5" placeholder="0-20">
        </div>
        <div class="form-group-small">
          <label for="credit${counter}">Crédit</label>
          <input type="number" id="credit${counter}" min="1" max="30" placeholder="ECTS">
        </div>
      </div>
    `;

        ueContainer.appendChild(newUeSection);

        // Ajout des écouteurs d'événements
        const removeBtn = newUeSection.querySelector('.remove-ue');
        removeBtn.addEventListener('click', function() {
            newUeSection.remove();
            updateAverageAndCredit();
        });

        const newInputs = newUeSection.querySelectorAll('input[id^="note"], input[id^="credit"]');
        newInputs.forEach(input => {
            input.addEventListener('input', updateAverageAndCredit);
        });

        counter++;
    }

    // Calcul de la moyenne et total des crédits
    function updateAverageAndCredit() {
        let totalWeightedScore = 0;
        let totalCredits = 0;
        let allUEs = document.querySelectorAll('.ue-section');

        allUEs.forEach(ue => {
            const ueId = ue.dataset.ueId;
            const noteInput = document.getElementById(`note${ueId}`);
            const creditInput = document.getElementById(`credit${ueId}`);

            if (noteInput && creditInput && noteInput.value && creditInput.value) {
                const note = parseFloat(noteInput.value);
                const credit = parseInt(creditInput.value);

                if (!isNaN(note) && !isNaN(credit)) {
                    totalWeightedScore += note * credit;
                    totalCredits += credit;
                }
            }
        });

        const moyenne = totalCredits > 0 ? totalWeightedScore / totalCredits : 0;
        document.getElementById('moyenneValue').textContent = moyenne.toFixed(2);
        document.getElementById('creditTotal').textContent = totalCredits;

        // Mise à jour du statut
        updateStatus(moyenne);
    }

    // Mise à jour du statut en fonction de la moyenne
    function updateStatus(moyenne) {
        const statusElement = document.getElementById('statusValue');

        if (moyenne === 0) {
            statusElement.textContent = 'Non évalué';
            statusElement.className = 'status-indicator';
        } else if (moyenne >= 10) {
            statusElement.textContent = 'Admis';
            statusElement.className = 'status-indicator status-success';
        } else if (moyenne >= 8) {
            statusElement.textContent = 'Rattrapage';
            statusElement.className = 'status-indicator status-warning';
        } else {
            statusElement.textContent = 'Ajourné';
            statusElement.className = 'status-indicator status-danger';
        }
    }

    // Ajouter un étudiant à l'historique
    function ajouterEtudiant() {
        const num = document.getElementById('numEtudiant').value;
        const nom = document.getElementById('nom').value;
        const prenom = document.getElementById('prenom').value;
        const promo = document.getElementById('promotion').value;
        const moyenne = document.getElementById('moyenneValue').textContent;
        const status = document.getElementById('statusValue').textContent;
        const statusClass = document.getElementById('statusValue').className.split(' ')[1] || '';

        // Validation basique
        if (!num || !nom || !prenom) {
            alert('Veuillez remplir au moins le numéro, le nom et le prénom de l\'étudiant.');
            return;
        }

        // Récupération des UEs
        const ues = [];
        document.querySelectorAll('.ue-section').forEach(ue => {
            const ueId = ue.dataset.ueId;
            const ueTitle = document.getElementById(`ue${ueId}`).value;
            const note = document.getElementById(`note${ueId}`).value;
            const credit = document.getElementById(`credit${ueId}`).value;

            if (ueTitle && note && credit) {
                ues.push({
                    id: ueId,
                    title: ueTitle,
                    note: note,
                    credit: credit
                });
            }
        });

        // Création de l'objet étudiant
        const student = {
            num: num,
            nom: nom,
            prenom: prenom,
            promotion: promo,
            moyenne: moyenne,
            status: status,
            statusClass: statusClass,
            ues: ues,
            annee: document.getElementById('annee').value
        };

        // Ajout à l'array
        students.push(student);

        // Ajout à la table
        addStudentToTable(student);

        // Réinitialisation du formulaire
        resetForm();
    }

    // Ajouter un étudiant au tableau
    function addStudentToTable(student) {
        const table = document.getElementById('historiqueTable').getElementsByTagName('tbody')[0];
        const row = table.insertRow();

        row.innerHTML = `
      <td>${student.num}</td>
      <td>${student.nom}</td>
      <td>${student.prenom}</td>
      <td>${student.promotion}</td>
      <td>${student.moyenne}/20</td>
      <td><span class="status-indicator ${student.statusClass}">${student.status}</span></td>
      <td class="action-buttons">
        <button class="btn btn-primary btn-action" onclick="voirDetails(${students.length - 1})">Détails</button>
        <button class="btn btn-success btn-action" onclick="window.print()">Imprimer</button>
        <button class="btn btn-danger btn-action" onclick="supprimerLigne(this, ${students.length - 1})">Supprimer</button>
        <button class="btn btn-primary btn-action" onclick="modifierLigne(${students.length - 1})">Modifier</button>
      </td>
    `;
    }

    // Réinitialiser le formulaire
    function resetForm() {
        document.getElementById('numEtudiant').value = '';
        document.getElementById('nom').value = '';
        document.getElementById('prenom').value = '';
        document.getElementById('promotion').value = '';

        // Réinitialisation des UEs par défaut
        for (let i = 1; i <= 3; i++) {
            document.getElementById(`ue${i}`).value = '';
            document.getElementById(`note${i}`).value = '';
            document.getElementById(`credit${i}`).value = '';
        }

        // Suppression des UEs supplémentaires
        const additionalUEs = document.querySelectorAll('.ue-section[data-ue-id]');
        additionalUEs.forEach(ue => {
            if (parseInt(ue.dataset.ueId) > 3) {
                ue.remove();
            }
        });

        // Mise à jour des statistiques
        updateAverageAndCredit();
    }

    // Voir les détails d'un étudiant
    function voirDetails(index) {
        const student = students[index];
        const modal = document.getElementById('detailsModal');
        const content = document.getElementById('modalContent');

        // Création du contenu HTML
        let html = `
      <div>
        <h3>Informations générales</h3>
        <p><strong>Année académique:</strong> ${student.annee || 'Non spécifiée'}</p>
        <p><strong>Numéro:</strong> ${student.num}</p>
        <p><strong>Nom complet:</strong> ${student.nom} ${student.prenom}</p>
        <p><strong>Promotion:</strong> ${student.promotion}</p>
        <p><strong>Moyenne:</strong> ${student.moyenne}/20</p>
        <p><strong>Statut:</strong> <span class="status-indicator ${student.statusClass}">${student.status}</span></p>

        <h3>Unités d'enseignement</h3>
        <table>
          <thead>
            <tr>
              <th>UE</th>
              <th>Note</th>
              <th>Crédit</th>
            </tr>
          </thead>
          <tbody>
    `;

        // Ajout des UEs
        student.ues.forEach(ue => {
            html += `
        <tr>
          <td>${ue.title}</td>
          <td>${ue.note}/20</td>
          <td>${ue.credit} ECTS</td>
        </tr>
      `;
        });

        html += `
          </tbody>
        </table>
      </div>
    `;

        content.innerHTML = html;
        modal.style.display = 'flex';
    }

    // Modifier une ligne
    function modifierLigne(index) {
        const student = students[index];
        const modal = document.getElementById('editModal');

        // Remplir les champs du formulaire
        document.getElementById('editNumEtudiant').value = student.num;
        document.getElementById('editNom').value = student.nom;
        document.getElementById('editPrenom').value = student.prenom;
        document.getElementById('editPromotion').value = student.promotion;

        // Générer les champs UE
        const uesContainer = document.getElementById('editUesContainer');
        uesContainer.innerHTML = '';

        student.ues.forEach((ue, i) => {
            const ueDiv = document.createElement('div');
            ueDiv.className = 'ue-section';
            ueDiv.dataset.editUeId = i;

            ueDiv.innerHTML = `
        <div class="ue-header">
          <h3>UE ${parseInt(ue.id)}</h3>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="editUe${i}">Intitulé</label>
            <input type="text" id="editUe${i}" value="${ue.title}">
          </div>
          <div class="form-group-small">
            <label for="editNote${i}">Note</label>
            <input type="number" id="editNote${i}" min="0" max="20" step="0.5" value="${ue.note}">
          </div>
          <div class="form-group-small">
            <label for="editCredit${i}">Crédit</label>
            <input type="number" id="editCredit${i}" min="1" max="30" value="${ue.credit}">
          </div>
        </div>
      `;

            uesContainer.appendChild(ueDiv);
        });

        // Mémoriser l'index pour la sauvegarde
        editingRowIndex = index;

    // Afficher la

        // Toggle sidebar collapse
        document.getElementById('toggle-btn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        });

        // Toggle submenu visibility
        const menuItems = document.querySelectorAll('.menu-item[data-submenu]');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                const submenuId = this.getAttribute('data-submenu');
                const submenu = document.getElementById(submenuId);
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Toggle current submenu state
                this.setAttribute('aria-expanded', !isExpanded);
                submenu.classList.toggle('open');

                // Set active menu item
                menuItems.forEach(mi => {
                    if (mi !== this) {
                        mi.classList.remove('active');
                    }
                });
                this.classList.toggle('active');
            });
        });

        // Navbar item click effect
        const navbarItems = document.querySelectorAll('.navbar-item');
        navbarItems.forEach(item => {
            item.addEventListener('click', function() {
                navbarItems.forEach(ni => ni.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Submenu item click effect
        const submenuItems = document.querySelectorAll('.submenu-item');
        submenuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Prevent the click from bubbling up to parent elements
                e.stopPropagation();

                // Set active submenu item
                submenuItems.forEach(si => {
                    si.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
</script>
</body>
</html>