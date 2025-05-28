<?php
// File: src/Backend/Controller/DashboardController.php
namespace App\Backend\Controller;

use App\Backend\Model\TypeUtilisateur;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\RapportEtudiant;
// TODO: Add other necessary models like CompteRendu, Soutenance, etc. as needed for stats
use Config\Database;

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
        $userTypeId = $user['id_type_utilisateur'] ?? null;
        $userRoleLabel = $this->getUserRoleLabel($userTypeId);

        $_SESSION['user_role_label'] = $userRoleLabel;

        $pageTitle = 'Tableau de Bord - ' . $userRoleLabel;
        $menuItems = $this->getMenuItemsForRole($userRoleLabel);
        $dashboardData = $this->getDashboardDataForRole($userRoleLabel, $user);
        $contentView = $this->getDashboardContentViewForRole($userRoleLabel);

        $dataToRender = [
            'pageTitle' => $pageTitle,
            'menuItems' => $menuItems,
            'currentUser' => $user,
            'userRole' => $userRoleLabel,
            'contentView' => $contentView,
        ];

        $renderData = array_merge($dataToRender, $dashboardData);
        $this->render('src/Frontend/views/layout/app.php', $renderData);
    }

    private function getUserRoleLabel(?int $userTypeId): string
    {
        if ($userTypeId === null) return 'Invité';

        try {
            $pdo = Database::getInstance()->getConnection();
            $typeUtilisateurModel = new TypeUtilisateur($pdo);
            $typeInfo = $typeUtilisateurModel->find($userTypeId);
            // Ensure consistency, matching the switch cases below if they use "Administrateur Systeme"
            if ($typeInfo && $typeInfo['lib_type_utilisateur'] === 'Administrateur Système') {
                return 'Administrateur Systeme'; 
            }
            return $typeInfo ? $typeInfo['lib_type_utilisateur'] : 'Rôle Inconnu';
        } catch (\Exception $e) {
            error_log("Error fetching user role label: " . $e->getMessage());
            return 'Rôle Indisponible';
        }
    }

    private function getMenuItemsForRole(string $role): array
    {
        $iconDashboard = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 018.25 20.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.01H15.75A2.25 2.25 0 0113.5 18v-2.25z" /></svg>';
        $iconUsers = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>';
        $iconReports = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>';
        $iconProfile = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>';
        // TODO: Add other icons definitions if new essential menu items need them

        $baseItems = [
            ['label' => 'Tableau de Bord', 'url' => '/dashboard', 'icon' => $iconDashboard],
        ];

        $roleSpecificItems = [];
        // TODO: Load role-specific menu items dynamically based on permissions.

        switch ($role) {
            case 'Administrateur Systeme': 
                $roleSpecificItems = [
                    ['label' => 'Gestion Utilisateurs', 'url' => '/admin/users', 'icon' => $iconUsers],
                ];
                break;
            case 'Étudiant':
                $roleSpecificItems = [
                    ['label' => 'Mes Rapports', 'url' => '/student/my-reports', 'icon' => $iconReports],
                    ['label' => 'Mon Profil', 'url' => '/student/profile', 'icon' => $iconProfile],
                ];
                break;
            case 'Enseignant':
                $roleSpecificItems = [
                    ['label' => 'Rapports à Évaluer', 'url' => '/teacher/reports-to-evaluate', 'icon' => $iconReports], 
                    ['label' => 'Mon Profil', 'url' => '/teacher/profile', 'icon' => $iconProfile],
                ];
                break;
            case 'Personnel Administratif':
                $roleSpecificItems = [
                    ['label' => 'Validation Rapports', 'url' => '/staff/validate-reports', 'icon' => $iconReports], 
                ];
                break;
        }
        return array_merge($baseItems, $roleSpecificItems);
    }

    private function getDashboardDataForRole(string $role, array $userSessionData): array
    {
        $pdo = Database::getInstance()->getConnection();
        $userModel = new Utilisateur($pdo);
        $reportModel = new RapportEtudiant($pdo);
        // TODO: Instantiate other models like CompteRendu, Soutenance as needed for specific stats.

        $data = ['stats' => [], 'alerts' => [], 'notifications' => [], 'recent_activity' => []];

        switch ($role) {
            case 'Administrateur Systeme': 
                $data['stats']['active_users'] = $userModel->count(['actif' => 1]);
                // Assuming type IDs: 2 for Etudiant, 3 for Enseignant, 4 for Personnel Administratif
                // These IDs should ideally be constants or fetched/mapped dynamically if they can change.
                $data['stats']['active_students'] = $userModel->count(['actif' => 1, 'id_type_utilisateur' => 2]); 
                $data['stats']['active_teachers'] = $userModel->count(['actif' => 1, 'id_type_utilisateur' => 3]); 
                $data['stats']['active_staff'] = $userModel->count(['actif' => 1, 'id_type_utilisateur' => 4]); 
                
                // TODO: Implement reports_submitted_year with date filtering if BaseModel allows, or use a raw query.
                $data['stats']['reports_submitted_year'] = $reportModel->count([]); // Counts all reports for now
                $data['stats']['reports_pending_conformity'] = $reportModel->count(['statut_rapport' => 'SOUMIS_EN_ATTENTE_CONFORMITE']); // Use actual status from your app
                
                $data['stats']['reports_in_commission'] = 0; // TODO: Implement this statistic (likely needs join or specific query)
                $data['stats']['reports_validated_commission'] = 0; // TODO: Implement this statistic
                $data['stats']['defenses_planned'] = 0; // TODO: Implement (requires SoutenanceModel)
                $data['stats']['defenses_done_year'] = 0; // TODO: Implement (requires SoutenanceModel with date filtering)
                $data['stats']['pvs_pending'] = 0; // TODO: Implement (requires CompteRenduModel and status)
                break;
            case 'Étudiant':
                $studentId = $userSessionData['id_etudiant'] ?? null; 
                if ($studentId) {
                     $myLastReport = $reportModel->findOneBy(['id_etudiant' => $studentId], ['*'], 'date_soumission DESC');
                     $data['my_report_status'] = $myLastReport ? $myLastReport['statut_rapport'] : 'Aucun rapport déposé';
                } else {
                    $data['my_report_status'] = 'ID etudiant non trouve en session.'; // Corrected typo
                }
                $data['next_deadline'] = 'N/A'; // TODO: Implement dynamic deadline logic
                break;
            case 'Enseignant':
                $teacherId = $userSessionData['id_enseignant'] ?? null; 
                // TODO: Implement reports_to_validate_count. This is complex.
                $data['reports_to_validate_count'] = 0; 
                // TODO: Implement supervision_count.
                $data['supervision_count'] = 0; 
                // TODO: Implement upcoming_defenses_jury.
                $data['upcoming_defenses_jury'] = 0; 
                break;
            case 'Personnel Administratif':
                $data['stats']['reports_pending_conformity_staff'] = $reportModel->count(['statut_rapport' => 'SOUMIS_EN_ATTENTE_CONFORMITE']);
                // TODO: Implement defenses_to_schedule.
                $data['stats']['defenses_to_schedule'] = 0; 
                break;
        }
        return $data;
    }

    private function getDashboardContentViewForRole(string $role): string
    {
        $baseViewPath = ROOT_PATH . '/src/Frontend/views/dashboards/';

        switch ($role) {
            case 'Administrateur Systeme': 
                return $baseViewPath . 'admin_dashboard_content.php';
            case 'Étudiant':
                return $baseViewPath . 'student_dashboard_content.php';
            case 'Enseignant':
                return $baseViewPath . 'teacher_dashboard_content.php';
            case 'Personnel Administratif':
                return $baseViewPath . 'staff_dashboard_content.php';
            default:
                return $baseViewPath . 'default_dashboard_content.php';
        }
    }
}
