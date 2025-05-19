<?php
// File: src/Backend/Controller/DashboardController.php
namespace Backend\Controller;

// Assurez-vous que BaseController est correctement namespacé et inclus.
// use Backend\Controller\BaseController; (Si BaseController est dans le même namespace)
// ou use App\Controller\BaseController; (Si BaseController est dans un namespace App\Controller)

class DashboardController extends BaseController // Assurez-vous que BaseController existe et est hérité
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $_SESSION['user'];
        // Vous devriez avoir l'ID du type d'utilisateur, et potentiellement le libellé du rôle stocké en session.
        // Par exemple, $_SESSION['user']['id_type_utilisateur'] ou $_SESSION['user_role_label']
        // Pour cet exemple, je vais simuler le rôle. Remplacez par votre logique réelle.
        $userTypeId = $user['id_type_utilisateur'] ?? null; // Supposons que cela vient de la table utilisateur
        $userRoleLabel = $this->getUserRoleLabel($userTypeId); // Méthode pour obtenir le libellé du rôle

        $_SESSION['user_role_label'] = $userRoleLabel; // Stocker pour y accéder facilement dans les vues

        $pageTitle = 'Tableau de Bord - ' . $userRoleLabel;
        $menuItems = $this->getMenuItemsForRole($userRoleLabel);
        $dashboardData = $this->getDashboardDataForRole($userRoleLabel, $user);
        $contentView = $this->getDashboardContentViewForRole($userRoleLabel);

        $dataToRender = [
            'pageTitle' => $pageTitle,
            'menuItems' => $menuItems,
            'currentUser' => $user, // Pour le header
            'userRole' => $userRoleLabel, // Pour le layout/menu
            'contentView' => $contentView, // Chemin vers la vue spécifique du contenu du dashboard
        ];

        // Fusionner les données spécifiques au dashboard (stats, alertes, etc.)
        $renderData = array_merge($dataToRender, $dashboardData);

        // La méthode render est héritée de BaseController
        // Elle devrait prendre le chemin vers le layout principal
        $this->render('src/Frontend/views/layout/app.php', $renderData);
    }

    /**
     * Récupère le libellé du rôle basé sur l'ID du type d'utilisateur.
     * À adapter avec votre logique réelle (ex: requête à la table type_utilisateur).
     */
    private function getUserRoleLabel(?int $userTypeId): string
    {
        if ($userTypeId === null) return 'Invité'; // Ou un rôle par défaut

        // Simulé - En réalité, faites une requête à votre modèle TypeUtilisateurModel
        // $typeUtilisateurModel = new \Backend\Model\TypeUtilisateur(\Config\Database::getInstance()->getConnection());
        // $typeInfo = $typeUtilisateurModel->find($userTypeId); // ou getById($userTypeId)
        // return $typeInfo ? $typeInfo['lib_type_utilisateur'] : 'Rôle Inconnu';

        // Simulation pour l'exemple :
        switch ($userTypeId) {
            case 1: return 'Administrateur Système'; // Supposez que l'ID 1 est Admin
            case 2: return 'Étudiant'; // Supposez que l'ID 2 est Étudiant
            case 3: return 'Enseignant'; // Supposez que l'ID 3 est Enseignant
            case 4: return 'Personnel Administratif'; // Supposez que l'ID 4 est Personnel Admin
            default: return 'Utilisateur';
        }
    }

    /**
     * Construit le tableau des items de menu en fonction du rôle de l'utilisateur.
     */
    private function getMenuItemsForRole(string $role): array
    {
        // Icônes SVG (exemples de Heroicons - https://heroicons.com/)
        $iconDashboard = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 018.25 20.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.01H15.75A2.25 2.25 0 0113.5 18v-2.25z" /></svg>';
        $iconUsers = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>';
        $iconStudents = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" /></svg>';
        $iconTeachers = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>';
        $iconReports = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>';
        $iconSettings = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-1.003 1.11-.952l2.83 1.415M13.903 6.06c.291-.049.586-.072.893-.072a6.745 6.745 0 016.745 6.745c0 .307-.023.614-.072.893m-1.415 2.83c-.09.542-.56 1.003-1.11.952l-2.83-1.415M10.097 17.94c-.291.049-.586.072-.893.072a6.745 6.745 0 01-6.745-6.745c0-.307.023-.614.072-.893m1.415-2.83c.09-.542.56-1.003 1.11-.952l2.83 1.415M4.5 12.75a7.5 7.5 0 0115 0m-15 0a7.5 7.5 0 0015 0m-15 0H3m18 0h-1.5m-15 0c0-1.35.216-2.654.616-3.873m13.768 0c.399 1.22.616 2.523.616 3.873m-1.501 7.032a7.466 7.466 0 01-4.242 1.423 7.466 7.466 0 01-4.242-1.423m5.701-11.056a7.466 7.466 0 014.242-1.423 7.466 7.466 0 014.242 1.423m-5.701 11.056a7.466 7.466 0 004.242 1.423 7.466 7.466 0 004.242-1.423M12 10.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" /></svg>';
        $iconProfile = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>';


        $baseItems = [
            ['label' => 'Tableau de Bord', 'url' => '/dashboard', 'icon' => $iconDashboard],
        ];

        $roleSpecificItems = [];

        switch ($role) {
            case 'Administrateur Système':
                $roleSpecificItems = [
                    ['label' => 'Gestion Utilisateurs', 'url' => '/admin/users', 'icon' => $iconUsers],
                    ['label' => 'Étudiants', 'url' => '/admin/students', 'icon' => $iconStudents],
                    ['label' => 'Enseignants', 'url' => '/admin/teachers', 'icon' => $iconTeachers],
                    ['label' => 'Données Académiques', 'url' => '/admin/academic-data/years', 'icon' => $iconReports], // Exemple de sous-route
                    ['label' => 'Processus Soutenance', 'url' => '/admin/soutenance-process', 'icon' => $iconReports],
                    ['label' => 'Paramètres Système', 'url' => '/admin/settings', 'icon' => $iconSettings],
                    // ... autres liens admin
                ];
                break;
            case 'Étudiant':
                $roleSpecificItems = [
                    ['label' => 'Mes Rapports', 'url' => '/student/my-reports', 'icon' => $iconReports],
                    ['label' => 'Déposer un Rapport', 'url' => '/student/submit-report', 'icon' => $iconReports],
                    ['label' => 'Mon Profil', 'url' => '/student/profile', 'icon' => $iconProfile],
                ];
                break;
            case 'Enseignant':
                $roleSpecificItems = [
                    ['label' => 'Rapports à Évaluer', 'url' => '/teacher/reports-to-evaluate', 'icon' => $iconReports],
                    ['label' => 'Mes Encadrements', 'url' => '/teacher/my-supervisions', 'icon' => $iconReports],
                    ['label' => 'Mon Profil', 'url' => '/teacher/profile', 'icon' => $iconProfile],
                ];
                break;
            case 'Personnel Administratif':
                $roleSpecificItems = [
                    ['label' => 'Validation Rapports', 'url' => '/staff/validate-reports', 'icon' => $iconReports],
                    ['label' => 'Planification Soutenance', 'url' => '/staff/schedule-defenses', 'icon' => $iconSettings],
                ];
                break;
        }
        return array_merge($baseItems, $roleSpecificItems);
    }

    /**
     * Récupère les données spécifiques (KPIs, alertes, etc.) pour le dashboard du rôle.
     */
    private function getDashboardDataForRole(string $role, array $user): array
    {
        // Ici, vous feriez appel à vos modèles pour récupérer les données.
        // $pdo = \Config\Database::getInstance()->getConnection();
        // $userModel = new \Backend\Model\Utilisateur($pdo);
        // $reportModel = new \Backend\Model\RapportEtudiant($pdo);

        $data = ['stats' => [], 'alerts' => [], 'notifications' => [], 'recent_activity' => []];

        switch ($role) {
            case 'Administrateur Système':
                // Exemple de données pour l'admin (tiré de votre document)
                // $data['stats']['total_users'] = $userModel->countAll(); // Exemple
                // $data['stats']['active_users'] = $userModel->count(['actif' => 1]);
                // $data['stats']['reports_submitted_total'] = $reportModel->countAll();
                // $data['stats']['reports_pending_conformity'] = $reportModel->count(['statut' => 'En attente de conformité']);
                // ... et ainsi de suite pour tous les KPIs
                $data['stats'] = [ // Simulé
                    'active_users' => 150, 'active_students' => 100, 'active_teachers' => 30, 'active_staff' => 20,
                    'reports_submitted_year' => 50, 'reports_pending_conformity' => 12,
                    'reports_in_commission' => 8, 'reports_validated_commission' => 25,
                    'defenses_planned' => 5, 'defenses_done_year' => 20, 'pvs_pending' => 3,
                ];
                $data['alerts'][] = ['message' => '3 rapports dépassent les délais de validation.', 'type' => 'warning', 'link' => '/admin/reports/delayed'];
                $data['recent_activity'][] = ['user' => 'admin_user', 'action' => 'a créé un nouvel utilisateur', 'timestamp' => '2025-05-19 10:00:00'];
                break;
            case 'Étudiant':
                // $myLastReport = $reportModel->findOneBy(['id_etudiant' => $user['id_etudiant']], ['*'], 'date_depot DESC');
                // $data['my_report_status'] = $myLastReport ? $myLastReport['statut'] : 'Aucun rapport déposé';
                $data['my_report_status'] = 'En attente de validation de conformité'; // Simulé
                $data['next_deadline'] = 'Dépôt final: 30 Juin 2025';
                $data['notifications'][] = ['message' => 'Commentaire de votre encadrant sur la version 2.', 'date' => '18/05/2025'];
                break;
            case 'Enseignant':
                // $data['reports_to_validate_count'] = $reportModel->count(['id_validateur_assigne' => $user['id_enseignant'], 'statut' => 'En attente validation encadrant']);
                $data['reports_to_validate_count'] = 3; // Simulé
                $data['supervision_count'] = 5; // Simulé
                $data['upcoming_defenses_jury'] = 2; // Simulé
                break;
            case 'Personnel Administratif':
                // $data['stats']['reports_pending_conformity_staff'] = $reportModel->count(['statut' => 'En attente de conformité']);
                $data['stats']['reports_pending_conformity_staff'] = 12; // Simulé
                $data['stats']['defenses_to_schedule'] = 7; // Simulé
                break;
        }
        return $data;
    }

    /**
     * Retourne le chemin vers le fichier de vue du contenu du dashboard pour un rôle donné.
     */
    private function getDashboardContentViewForRole(string $role): string
    {
        $baseViewPath = ROOT_PATH . '/src/Frontend/views/dashboards/'; // Créez ce dossier

        switch ($role) {
            case 'Administrateur Système':
                return $baseViewPath . 'admin_dashboard_content.php';
            case 'Étudiant':
                return $baseViewPath . 'student_dashboard_content.php';
            case 'Enseignant':
                return $baseViewPath . 'teacher_dashboard_content.php';
            case 'Personnel Administratif':
                return $baseViewPath . 'staff_dashboard_content.php';
            default:
                return $baseViewPath . 'default_dashboard_content.php'; // Une vue par défaut
        }
    }
}
