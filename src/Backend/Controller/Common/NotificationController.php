<?php
namespace App\Backend\Controller\Common;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Notification\ServiceNotification; // Importer le service
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class NotificationController extends BaseController
{
    private ServiceNotification $notificationService;

    public function __construct(
        ServiceAuthentification $authService,
        ServicePermissions $permissionService,
        FormValidator $validator,
        ServiceNotification $notificationService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->notificationService = $notificationService;
    }

    /**
     * Affiche le panneau des notifications pour l'utilisateur connecté.
     * Inclut les notifications lues et non lues.
     */
    public function index(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            $notifications = $this->notificationService->recupererNotificationsUtilisateur($numeroUtilisateur, true); // Inclure les lues
            $nonLuesCount = $this->notificationService->compterNotificationsNonLues($numeroUtilisateur);

            $data = [
                'page_title' => 'Mes Notifications',
                'notifications' => $notifications,
                'non_lues_count' => $nonLuesCount
            ];
            $this->render('common/notifications_panel', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des notifications: " . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }

    /**
     * Marque une notification spécifique comme lue.
     * Cette méthode sera typiquement appelée via AJAX.
     * @param string $idNotificationTemplate L'ID du modèle de notification.
     * @param string $dateReception La date exacte de réception de la notification (pour clé composite).
     */
    public function markAsRead(string $idNotificationTemplate, string $dateReception): void
    {
        $this->requireLogin();

        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroUtilisateur = $currentUser['numero_utilisateur'];

        try {
            $this->notificationService->marquerNotificationCommeLue($numeroUtilisateur, $idNotificationTemplate, $dateReception);
            // Retourner une réponse JSON pour les appels AJAX
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Notification marquée comme lue.']);
            exit();
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erreur: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * Supprime (archive) une notification.
     * Cette méthode sera typiquement appelée via AJAX.
     * @param string $idNotificationTemplate L'ID du modèle de notification.
     * @param string $dateReception La date exacte de réception de la notification (pour clé composite).
     */
    public function deleteNotification(string $idNotificationTemplate, string $dateReception): void
    {
        $this->requireLogin();

        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroUtilisateur = $currentUser['numero_utilisateur'];

        try {
            // Pour la suppression (archivage), nous allons utiliser la méthode `supprimerParClesInternes`
            // du modèle `Recevoir` via une méthode dédiée dans le `ServiceNotification` ou directement.
            // Ici, on va appeler `archiverAnciennesNotificationsLues` pour simuler un nettoyage plus global.
            // Pour une suppression ciblée, on ferait ceci :
            $pdo = $this->authService->getUtilisateurModel()->getDb();
            $recevoirModel = new \App\Backend\Model\Recevoir($pdo);
            if (!$recevoirModel->supprimerParClesInternes([
                'numero_utilisateur' => $numeroUtilisateur,
                'id_notification' => $idNotificationTemplate,
                'date_reception' => $dateReception
            ])) {
                throw new OperationImpossibleException("Échec de la suppression de la notification.");
            }

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Notification supprimée.']);
            exit();
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erreur: ' . $e->getMessage()]);
            exit();
        }
    }

    // Les méthodes create() et update() génériques du template initial sont à supprimer.
    /*
    public function create(): void {}
    public function update($id): void {}
    */
}