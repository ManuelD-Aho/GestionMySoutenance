<?php

namespace Backend\Controller\Admin;

use Backend\Controller\BaseController;
use Backend\Model\AnneeAcademique;
use Config\Database;

class AnneeAcademiqueController extends BaseController
{
    private $anneeAcademiqueModel;

    public function __construct()
    {
        // BaseController does not have a constructor that takes arguments.
        // Model is instantiated in methods where needed.
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Simple role check, consider a more robust RBAC system for a real application
        if (!isset($_SESSION['user']) || $_SESSION['user_role_label'] !== 'Administrateur Systeme') {
            // Redirect to login or show an error page
            header('Location: /login?error=unauthorized');
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $this->anneeAcademiqueModel = new AnneeAcademique($pdo); 

        $annees = $this->anneeAcademiqueModel->findAll();

        // Data for rendering the view
        $data = [
            'annees' => $annees,
            'pageTitle' => 'Gestion des Années Académiques',
            'userRole' => $_SESSION['user_role_label'] ?? '', 
            'currentUser' => $_SESSION['user'] ?? null, 
            'menuItems' => $this->getAdminMenuItems() 
        ];
        
        // Render the main layout with the specific content view
        // The BaseController's render method handles including the layout and passing data to it.
        // The 'contentView' variable inside $data will point to the specific view for this action.
        // However, the provided BaseController structure might expect $contentView path directly.
        // Let's assume render takes the view path and data array.
        // $this->render('admin/annee_academique/index.php', $data);
        // The current BaseController's render takes $viewFile directly as the main content.
        // The layout is fixed within BaseController. To pass data to layout, it must be part of $data.
        // For this task, we are creating the view that will be *included* by the layout.
        // So, the render call in the controller should point to the main layout,
        // and the $data array should include a key that the layout uses to include the specific content view path.
        // This seems to be how the DashboardController was structured.
        // Let's adjust to pass 'contentView' path in $data for the main layout to use.
        
        $data['contentView'] = 'src/Frontend/views/admin/annee_academique/index.php';
        // The main layout 'app.php' should then use $contentView to include the actual page content.
        // This render method is from BaseController, let's assume it's: render($layout, $viewData)
        $this->render('src/Frontend/views/layout/app.php', $data);
    }
    
    // Placeholder for admin menu items - this would typically be more dynamic
    // and likely part of a BaseAdminController or a service.
    private function getAdminMenuItems(): array
    {
         return [
            ['label' => 'Tableau de Bord', 'url' => '/dashboard', 'icon' => '<i class="fas fa-tachometer-alt"></i>'], // Example icon
            ['label' => 'Années Académiques', 'url' => '/admin/annees-academiques', 'icon' => '<i class="fas fa-calendar-alt"></i>'], // Example icon
            // Add other admin menu items here, e.g., Users, Settings etc.
            // ['label' => 'Utilisateurs', 'url' => '/admin/users', 'icon' => '<i class="fas fa-users"></i>'],
        ];
    }
}
?>
