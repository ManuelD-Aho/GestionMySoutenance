<?php
namespace App\Backend\Controller\PersonnelAdministratif;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Conformite\ServiceConformite; // Importer le service
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique; // Importer le service
use App\Backend\Service\Reclamation\ServiceReclamation; // Importer le service
use App\Backend\Service\Notification\ServiceNotification; // Importer le service
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class PersonnelDashboardController extends BaseController
{
    private ServiceConformite $conformiteService;
    private ServiceGestionAcademique $gestionAcadService;
    private ServiceReclamation $reclamationService;
    private ServiceNotification $notificationService;

    public function __construct(
        ServiceAuthentication    $authService,
        ServicePermissions       $permissionService,
        FormValidator            $validator,
        ServiceConformite        $conformiteService,
        ServiceGestionAcademique $gestionAcadService,
        ServiceReclamation       $reclamationService,
        ServiceNotification      $notificationService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->conformiteService = $conformiteService;
        $this->gestionAcadService = $gestionAcadService;
        $this->reclamationService = $reclamationService;
        $this->notificationService = $notificationService;
    }

    /**
     * Affiche le tableau de bord pour le personnel administratif.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_PERS_ADMIN') {
                throw new OperationImpossibleException("Accès refusé. Non personnel administratif.");
            }
            $numeroPersonnel = $currentUser['numero_utilisateur'];

            // Rapports à vérifier (pour l'agent de conformité)
            $rapportsAVerifierCount = count($this->conformiteService->recupererRapportsEnAttenteDeVerification());

            // Réclamations en cours (pour le RS)
            $reclamationsEnCoursCount = count($this->reclamationService->recupererToutesReclamations(['id_statut_reclamation' => ['operator' => 'in', 'values' => ['RECLAM_RECUE', 'RECLAM_EN_COURS']]]));

            // Pénalités à régulariser (pour le RS)
            // Note: La méthode doit être dans GestionAcademique
            // $penalitesADueCount = count($this->gestionAcadService->listerPenalites(['id_statut_penalite' => 'PEN_DUE']));

            // Notifications non lues
            $notificationsNonLuesCount = $this->notificationService->compterNotificationsNonLues($numeroPersonnel);

            $data = [
                'page_title' => 'Tableau de Bord Personnel Administratif',
                'rapports_a_verifier_count' => $rapportsAVerifierCount,
                'reclamations_en_cours_count' => $reclamationsEnCoursCount,
                'penalites_a_due_count' => 0, // Placeholder, à implémenter
                'notifications_non_lues_count' => $notificationsNonLuesCount,
                // Liens rapides vers les modules spécifiques
                'links' => [
                    ['label' => 'Vérification Conformité', 'url' => '/dashboard/personnel-admin/conformite'],
                    ['label' => 'Gestion Scolarité', 'url' => '/dashboard/personnel-admin/scolarite'],
                    ['label' => 'Gestion Réclamations', 'url' => '/dashboard/personnel-admin/reclamations'], // Peut être une section de Scolarité
                    ['label' => 'Messagerie Interne', 'url' => '/dashboard/personnel-admin/communication'],
                ]
            ];
            $this->render('PersonnelAdministratif/dashboard_personnel', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement du tableau de bord: " . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}