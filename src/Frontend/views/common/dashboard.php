<?php
// src/Frontend/views/common/dashboard.php

// Les variables passées par le contrôleur sont disponibles ici,
// y compris $data['dashboard_content'] qui contient 'type'.
$dashboardType = $data['dashboard_content']['type'] ?? 'default';

// Inclure la vue spécifique au type d'utilisateur
switch ($dashboardType) {
    case 'admin':
        include __DIR__ . '/../Administration/dashboard_admin.php'; // Chemin relatif à common/dashboard.php
        break;
    case 'etudiant':
        include __DIR__ . '/../Etudiant/dashboard_etudiant.php'; // Chemin relatif à common/dashboard.php
        break;
    case 'enseignant': // Assurez-vous d'avoir une vue pour 'enseignant' ou 'commission'
        include __DIR__ . '/../Commission/dashboard_commission.php'; // Ou une vue spécifique aux enseignants
        break;
    case 'personnel':
        include __DIR__ . '/../PersonnelAdministratif/dashboard_personnel.php'; // Chemin relatif à common/dashboard.php
        break;
    default:
        // Contenu par défaut si le type n'est pas reconnu ou pour les invités
        echo "<h1>Bienvenue sur votre Tableau de Bord !</h1>";
        echo "<p>Contenu spécifique au rôle non disponible ou rôle non défini.</p>";
        break;
}
?>