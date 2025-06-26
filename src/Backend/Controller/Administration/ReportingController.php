<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\ReportingAdmin\ServiceReportingAdmin; // Importer le service
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour récupérer les années académiques si besoin de filtrer

class ReportingController extends BaseController
{
    private ServiceReportingAdmin $reportingService;
    private ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceReportingAdmin       $reportingService, // Injection
        ServiceConfigurationSysteme $configService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->reportingService = $reportingService;
        $this->configService = $configService;
    }

    /**
     * Affiche la page des rapports.
     * Présente les différents types de rapports disponibles.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_REPORTING_ACCEDER'); // Permission générale

        try {
            // Récupérer les données pour le rapport de taux de validation
            $anneesAcademiques = $this->configService->listerAnneesAcademiques();
            $selectedAnnee = $this->getRequestData('annee_academique_id');
            $rapportTauxValidation = $this->reportingService->genererRapportTauxValidation($selectedAnnee);

            // Récupérer les données pour le rapport de délais
            $rapportDelais = $this->reportingService->genererRapportDelaisMoyensParEtape();

            // Récupérer les données pour les statistiques d'utilisation
            $statistiquesUtilisation = $this->reportingService->genererStatistiquesUtilisation();

            $data = [
                'page_title' => 'Rapports d\'Administration',
                'annees_academiques' => $anneesAcademiques,
                'selected_annee' => $selectedAnnee,
                'rapport_taux_validation' => $rapportTauxValidation,
                'rapport_delais' => $rapportDelais,
                'statistiques_utilisation' => $statistiquesUtilisation
            ];
            $this->render('Administration/reporting_admin', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des rapports: " . $e->getMessage());
            $this->redirect('/dashboard/admin');
        }
    }

    /**
     * Gère le filtrage ou la régénération des rapports.
     * Redirige vers la page d'index avec les paramètres de filtre.
     */
    public function filterReports(): void
    {
        $this->requirePermission('TRAIT_ADMIN_REPORTING_ACCEDER'); // La même permission que l'accès au reporting

        if ($this->isPostRequest()) {
            $anneeAcademiqueId = $this->getRequestData('annee_academique_id');
            $this->redirect("/dashboard/admin/reporting?annee_academique_id={$anneeAcademiqueId}");
        } else {
            $this->redirect('/dashboard/admin/reporting');
        }
    }

    // Les méthodes create(), update(), delete() ne sont pas pertinentes pour un contrôleur de reporting
    // et devraient être supprimées.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}