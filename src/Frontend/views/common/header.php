<?php
// src/Frontend/views/common/header_dashboard.php

// Les icônes proviennent de Font Awesome. Assurez-vous qu'il est lié dans layout/app.php.
// Vous pouvez injecter des variables comme le nom de l'utilisateur via le contrôleur principal.
$userName = $_SESSION['user_name'] ?? 'Utilisateur'; // Exemple: Récupérer le nom de l'utilisateur de la session
$userRole = $_SESSION['user_role'] ?? 'Rôle'; // Exemple: Récupérer le rôle de l'utilisateur de la session
?>

<header class="dashboard-header">
    <div class="dashboard-header-left">
        <button class="menu-toggle" aria-label="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="header-title">
            <span class="page-title">Dashboard</span> <!-- Ceci pourrait être dynamique selon la page actuelle -->
        </div>
    </div>
    <div class="dashboard-header-center">
        <div class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Rechercher n'importe quoi..." class="search-input">
        </div>
    </div>
    <div class="dashboard-header-right">
        <div class="icon-group">
            <button class="icon-btn" aria-label="Notifications">
                <i class="fas fa-bell"></i>
            </button>
            <button class="icon-btn" aria-label="Settings">
                <i class="fas fa-cog"></i>
            </button>
        </div>
        <div class="user-profile">
            <div class="user-avatar">
                <img src="https://placehold.co/40x40/cccccc/ffffff?text=U" alt="User Avatar"> <!-- Placeholder pour l'avatar -->
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($userRole); ?></div>
            </div>
            <button class="icon-btn user-dropdown-toggle" aria-label="User Options">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </div>
</header>

<style>
    /* Styles pour le Header du Dashboard */
    /* Ces styles sont inclus directement pour la démonstration.
       Il est FORTEMENT RECOMMANDÉ de les déplacer dans un fichier CSS externe
       (par exemple, `Public/assets/css/dashboard_header.css`)
       et de le lier dans `src/Frontend/views/layout/app.php`. */

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
        background-color: #ffffff; /* Fond blanc comme sur l'image */
        border-bottom: 1px solid #e0e0e0; /* Légère bordure inférieure */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); /* Ombre douce */
        border-radius: 8px; /* Coins arrondis */
        margin: 15px; /* Marge pour se détacher des bords */
    }

    .dashboard-header-left, .dashboard-header-center, .dashboard-header-right {
        display: flex;
        align-items: center;
    }

    .dashboard-header-left .menu-toggle {
        background: none;
        border: none;
        font-size: 1.2em;
        color: #333;
        cursor: pointer;
        margin-right: 20px;
        padding: 8px;
        border-radius: 50%;
        transition: background-color 0.3s ease;
    }

    .dashboard-header-left .menu-toggle:hover {
        background-color: #f0f0f0;
    }

    .dashboard-header-left .header-title .page-title {
        font-size: 1.5em;
        font-weight: 600;
        color: #333;
    }

    .dashboard-header-center .search-bar {
        display: flex;
        align-items: center;
        background-color: #f0f0f0;
        border-radius: 20px; /* Bords très arrondis pour le champ de recherche */
        padding: 8px 15px;
    }

    .search-bar .search-icon {
        color: #888;
        margin-right: 10px;
    }

    .search-bar .search-input {
        border: none;
        background: none;
        outline: none;
        font-size: 1em;
        padding: 0;
        color: #333;
        width: 250px; /* Largeur fixe pour l'exemple */
    }

    .search-bar .search-input::placeholder {
        color: #aaa;
    }

    .dashboard-header-right .icon-group .icon-btn {
        background: none;
        border: none;
        font-size: 1.1em;
        color: #555;
        cursor: pointer;
        padding: 10px;
        border-radius: 50%;
        margin-left: 10px;
        transition: background-color 0.3s ease;
    }

    .dashboard-header-right .icon-group .icon-btn:hover {
        background-color: #f0f0f0;
    }

    .dashboard-header-right .user-profile {
        display: flex;
        align-items: center;
        margin-left: 20px;
        background-color: #f8f8f8; /* Fond légèrement grisé pour le profil */
        padding: 8px 15px;
        border-radius: 25px; /* Forme de pilule */
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .dashboard-header-right .user-profile:hover {
        background-color: #e0e0e0;
    }

    .user-profile .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 10px;
    }

    .user-profile .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-profile .user-info {
        display: flex;
        flex-direction: column;
        margin-right: 10px;
    }

    .user-profile .user-info .user-name {
        font-weight: 600;
        color: #333;
    }

    .user-profile .user-info .user-role {
        font-size: 0.8em;
        color: #777;
    }

    .user-profile .user-dropdown-toggle {
        background: none;
        border: none;
        color: #555;
        cursor: pointer;
        font-size: 0.9em;
        padding: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .dashboard-header {
            padding: 10px 15px;
        }
        .dashboard-header-center .search-bar {
            width: 100%; /* Permet à la barre de recherche de s'adapter */
            margin-left: 10px;
        }
        .search-bar .search-input {
            width: 100%; /* Laisser le champ de recherche s'étirer */
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-wrap: wrap; /* Permet aux éléments de passer à la ligne */
            padding: 10px;
        }
        .dashboard-header-center {
            order: 3; /* Déplace la barre de recherche en dessous sur mobile */
            flex-basis: 100%; /* Prend toute la largeur */
            margin-top: 10px;
        }
        .dashboard-header-right {
            margin-left: auto;
        }
        .dashboard-header-left .header-title .page-title {
            font-size: 1.2em;
        }
        .user-profile {
            padding: 5px 10px;
        }
        .user-profile .user-info {
            display: none; /* Cache le nom et rôle sur les petits écrans */
        }
    }
</style>
