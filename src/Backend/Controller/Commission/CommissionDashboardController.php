<?php
namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Commission\ServiceCommission; // Importer le service
use App\Backend\Service\Notification\ServiceNotification; // Importer le service
use App\Backend\Exception\ElementNonTrouveException;

class CommissionDashboardController extends BaseController
{
    private ServiceCommission $commissionService;
    private ServiceNotification $notificationService;

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator,
        ServiceCommission     $commissionService, // Injection
        ServiceNotification   $notificationService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->commissionService = $commissionService;
        $this->notificationService = $notificationService;
    }

    /**
     * Affiche le tableau de bord de la commission.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) {
                throw new ElementNonTrouveException("Utilisateur connecté non trouvé."); // Ne devrait pas arriver avec requirePermission
            }
            $numeroEnseignant = $currentUser['numero_utilisateur']; // L'ID de l'utilisateur est le numéro d'enseignant pour ce rôle

            // Rapports à traiter par le membre de la commission
            $rapportsATraiter = $this->commissionService->recupererRapportsAssignedToJury($numeroEnseignant);

            // PV en attente de validation par ce membre
            $pvAValider = $this->commissionService->listerPvEnAttenteValidationParMembre($numeroEnseignant); // Nouvelle méthode à ajouter au service

            // Sessions de validation en cours ou planifiées
            $sessionsEnCours = $this->commissionService->listerSessionsValidation(['statut_session' => 'En cours']); // Nouvelle méthode
            $sessionsPlanifiees = $this->commissionService->listerSessionsValidation(['statut_session' => 'Planifiee']); // Nouvelle méthode

            // Notifications non lues
            $notificationsNonLues = $this->notificationService->compterNotificationsNonLues($numeroEnseignant);


            $data = [
                'page_title' => 'Tableau de Bord Commission',
                'rapports_a_traiter' => $rapportsATraiter,
                'pv_a_valider' => $pvAValider,
                'sessions_en_cours' => $sessionsEnCours,
                'sessions_planifiees' => $sessionsPlanifiees,
                'notifications_non_lues_count' => $notificationsNonLues,
                // Ajoutez d'autres données nécessaires
            ];
            $this->render('Commission/dashboard_commission', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement du tableau de bord: " . $e->getMessage());
            $this->redirect('/dashboard'); // Rediriger vers le dashboard général
        }
    }

    // Les méthodes create(), update(), delete() ne sont pas pertinentes pour un contrôleur de tableau de bord
    // et devraient être supprimées.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}