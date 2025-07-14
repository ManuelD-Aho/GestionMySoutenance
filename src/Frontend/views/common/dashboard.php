<?php
// /src/Frontend/views/common/dashboard.php

// Cette vue agit comme un routeur pour afficher le tableau de bord approprié.
// Les variables $data, $user, etc., sont disponibles car elles sont extraites
// par le BaseController avant l'inclusion de cette vue.

$dashboardType = $dashboard_content['type'] ?? 'default';

switch ($dashboardType) {
    case 'admin':
        // Inclut la vue spécifique au tableau de bord de l'administrateur
        include __DIR__ . '/../Administration/dashboard_admin.php';
        break;
    case 'etudiant':
        // Inclut la vue spécifique au tableau de bord de l'étudiant
        include __DIR__ . '/../Etudiant/dashboard_etudiant.php';
        break;
    case 'enseignant': // Gère à la fois les enseignants et les membres de la commission
        // Inclut la vue spécifique au tableau de bord de la commission
        include __DIR__ . '/../Commission/dashboard_commission.php';
        break;
    case 'personnel':
        // Inclut la vue spécifique au tableau de bord du personnel administratif
        include __DIR__ . '/../PersonnelAdministratif/dashboard_personnel.php';
        break;
    default:
        // Un contenu par défaut si le rôle n'est pas reconnu ou pour les invités
        echo '<div class="card bg-base-100 shadow-xl"><div class="card-body">';
        echo '<h2 class="card-title">Bienvenue !</h2>';
        echo "<p>Votre tableau de bord est en cours de construction. Rôle non reconnu : " . e($dashboardType) . "</p>";
        echo '</div></div>';
        break;
}
?>