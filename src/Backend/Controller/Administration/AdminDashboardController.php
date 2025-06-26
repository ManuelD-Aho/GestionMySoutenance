<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\ReportingAdmin\ServiceReportingAdmin; // Importer le service
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Importer le service

class AdminDashboardController extends BaseController
{
    private ServiceReportingAdmin $reportingService;
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(
        ServiceAuthentication   $authService,
        ServicePermissions      $permissionService,
        FormValidator           $validator,
        ServiceReportingAdmin   $reportingService, // Injection
        ServiceSupervisionAdmin $supervisionService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->reportingService = $reportingService;
        $this->supervisionService = $supervisionService;
    }

    /**
     * Affiche le tableau de bord de l'administrateur.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_DASHBOARD_ACCEDER'); // Exiger la permission d'accéder au dashboard admin

        try {
            $statistiquesRapports = $this->reportingService->genererRapportTauxValidation();
            $statistiquesUtilisation = $this->reportingService->genererStatistiquesUtilisation();
            $globalRapportsStats = $this->supervisionService->obtenirStatistiquesGlobalesRapports();

            $data = [
                'page_title' => 'Tableau de Bord Administrateur',
                'statistiques_rapports' => $statistiquesRapports,
                'statistiques_utilisation' => $statistiquesUtilisation,
                'global_rapports_stats' => $globalRapportsStats
                // Ajoutez d'autres données nécessaires pour le tableau de bord
            ];
            $this->render('Administration/dashboard_admin', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement du tableau de bord: " . $e->getMessage());
            // Rediriger vers une page d'erreur générique ou de déconnexion si l'erreur est grave
            $this->redirect('/dashboard'); // Rediriger vers le dashboard principal qui gère les rôles
        }
    }

    // Les méthodes create(), update(), delete() ne sont pas pertinentes pour un contrôleur de tableau de bord
    // et devraient être supprimées si elles ne sont pas utilisées pour des ressources spécifiques.
    // Si elles sont là, c'est que ce contrôleur est un contrôleur "ressource" pour AdminDashboard, ce qui est peu probable.
    // À SUPPRIMER si ce contrôleur ne fait QUE de l'affichage de dashboard et du reporting.
    /*
    public function create(): void
    {
        // ...
    }

    public function update($id): void
    {
        // ...
    }

    public function delete($id): void
    {
        // ...
    }
    */
}