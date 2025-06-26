<?php
namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Rapport\ServiceRapport; // Importer le service
use App\Backend\Service\Notification\ServiceNotification; // Importer le service
use App\Backend\Service\Reclamation\ServiceReclamation; // Importer le service
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique; // Importer le service
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour l'année académique active
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class EtudiantDashboardController extends BaseController
{
    private ServiceRapport $rapportService;
    private ServiceNotification $notificationService;
    private ServiceReclamation $reclamationService;
    private ServiceGestionAcademique $gestionAcadService;
    private ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceRapport              $rapportService,
        ServiceNotification         $notificationService,
        ServiceReclamation          $reclamationService,
        ServiceGestionAcademique    $gestionAcadService,
        ServiceConfigurationSysteme $configService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->rapportService = $rapportService;
        $this->notificationService = $notificationService;
        $this->reclamationService = $reclamationService;
        $this->gestionAcadService = $gestionAcadService;
        $this->configService = $configService;
    }

    /**
     * Affiche le tableau de bord de l'étudiant.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_DASHBOARD_ACCEDER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }
            $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

            // Informations sur le rapport (s'il existe)
            $rapport = $this->rapportService->recupererInformationsRapportComplet($this->getMostRecentRapportId($numeroCarteEtudiant)); // A affiner pour récupérer le rapport le plus récent/actif

            // Notifications non lues
            $notificationsNonLuesCount = $this->notificationService->compterNotificationsNonLues($numeroCarteEtudiant);

            // Réclamations en cours
            $reclamationsEnCours = $this->reclamationService->recupererReclamationsEtudiant($numeroCarteEtudiant);
            $reclamationsNonClotureesCount = count(array_filter($reclamationsEnCours, fn($r) => $r['id_statut_reclamation'] !== 'RECLAM_CLOTUREE'));

            // Eligibilité à la soumission du rapport
            $anneeAcademiqueActive = $this->configService->recupererAnneeAcademiqueActive(); // Assurez-vous que cette méthode existe dans configService
            $isEligibleForSubmission = false;
            if ($anneeAcademiqueActive) {
                $isEligibleForSubmission = $this->gestionAcadService->estEtudiantEligibleSoumission($numeroCarteEtudiant, $anneeAcademiqueActive['id_annee_academique']);
            }

            $data = [
                'page_title' => 'Mon Tableau de Bord',
                'rapport' => $rapport,
                'notifications_non_lues_count' => $notificationsNonLuesCount,
                'reclamations_non_cloturees_count' => $reclamationsNonClotureesCount,
                'is_eligible_for_submission' => $isEligibleForSubmission,
                'annee_academique_active_libelle' => $anneeAcademiqueActive['libelle_annee_academique'] ?? 'Non définie',
                // Ajoutez d'autres données pertinentes au tableau de bord de l'étudiant
            ];
            $this->render('Etudiant/dashboard_etudiant', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de votre tableau de bord: " . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }

    /**
     * Récupère l'ID du rapport le plus récent ou actif de l'étudiant.
     * Cette méthode est un placeholder et devrait être affinée dans ServiceRapport
     * pour gérer les brouillons ou les rapports en cours.
     * @param string $numeroCarteEtudiant
     * @return string|null
     */
    private function getMostRecentRapportId(string $numeroCarteEtudiant): ?string
    {
        // Idéalement, une méthode dans ServiceRapport qui trouverait le rapport pertinent (ex: le brouillon, ou le dernier soumis)
        // Pour l'instant, on va chercher n'importe quel rapport pour l'étudiant
        $rapports = $this->rapportService->listerRapportsParCriteres(['numero_carte_etudiant' => $numeroCarteEtudiant], ['id_rapport_etudiant'], 'AND', 'date_derniere_modif DESC', 1);
        return $rapports[0]['id_rapport_etudiant'] ?? null;
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}