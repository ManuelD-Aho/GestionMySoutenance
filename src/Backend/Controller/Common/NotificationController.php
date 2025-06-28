<?php
namespace App\Backend\Controller\Common;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentification; // Renommé pour correspondre à votre fichier
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class NotificationController extends BaseController
{
    private ServiceNotification $notificationService;

    public function __construct(
        ServiceAuthentification $authService, // Renommé
        ServicePermissions    $permissionService,
        FormValidator         $validator,
        ServiceNotification   $notificationService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->notificationService = $notificationService;
    }

    /**
     * Affiche la page complète des notifications pour l'utilisateur connecté.
     * Cette méthode est pour le rendu d'une page HTML, pas une API JSON.
     */
    public function index(): void // Renommé de showNotificationsPage, mais laissé 'index' car c'est la route GET principale.
    {
        $this->requireLogin();

        try {
            $currentUser = $this->authService->getUtilisateurConnecteComplet(); // Appeler via authService
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé ou non connecté."); }
            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            // Récupérer toutes les notifications pour la page dédiée (y compris les lues si désiré)
            $notifications = $this->notificationService->recupererNotificationsUtilisateur($numeroUtilisateur, false); // false pour inclure les lues
            $nonLuesCount = $this->notificationService->compterNotificationsNonLues($numeroUtilisateur);

            $data = [
                'page_title' => 'Mes Notifications',
                'notifications' => $notifications,
                'non_lues_count' => $nonLuesCount
            ];
            $this->render('common/notifications_panel', $data); // Assurez-vous que cette vue existe
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de la page des notifications: " . $e->getMessage());
            error_log("Erreur dans NotificationController::index: " . $e->getMessage());
            $this->redirect('/dashboard'); // Rediriger en cas d'erreur
        }
    }

    /**
     * API : Récupère et renvoie les notifications de l'utilisateur connecté au format JSON.
     * Cette méthode est appelée par le frontend JS pour le dropdown du header.
     */
    public function getNotifications(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté

        header('Content-Type: application/json'); // Définir l'en-tête pour le JSON

        try {
            $currentUser = $this->authService->getUtilisateurConnecteComplet(); // Appeler via authService
            if (!$currentUser) {
                echo json_encode(['error' => 'Non authentifié ou utilisateur introuvable.'], JSON_UNESCAPED_UNICODE);
                http_response_code(401);
                return;
            }

            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            // Récupérer un nombre limité de notifications pour le dropdown du header (par exemple, les 10 plus récentes)
            $notifications = $this->notificationService->recupererNotificationsUtilisateur($numeroUtilisateur, false, 10);

            // Pour le frontend, 'lue' doit être un booléen
            $formattedNotifications = array_map(function($notif) {
                $notif['lue'] = (bool)$notif['lue'];
                return $notif;
            }, $notifications);

            echo json_encode($formattedNotifications, JSON_UNESCAPED_UNICODE);
            http_response_code(200);

        } catch (\Exception $e) {
            error_log("Erreur API getNotifications: " . $e->getMessage());
            echo json_encode(['error' => 'Erreur interne du serveur lors de la récupération des notifications.'], JSON_UNESCAPED_UNICODE);
            http_response_code(500);
        }
    }

    /**
     * API : Marque une notification spécifique comme lue.
     * Attend un seul ID (id_reception) de la route.
     * @param string $idReception L'ID de réception de la notification.
     */
    public function markAsRead(string $idReception): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $currentUser = $this->authService->getUtilisateurConnecteComplet();
            if (!$currentUser) {
                echo json_encode(['status' => 'error', 'message' => 'Non authentifié.'], JSON_UNESCAPED_UNICODE);
                http_response_code(401);
                return;
            }

            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            // Vérifier que l'ID de réception appartient bien à l'utilisateur connecté
            $notification = $this->notificationService->recevoirModel->trouverParIdentifiant($idReception);
            if (!$notification || $notification['numero_utilisateur'] !== $numeroUtilisateur) {
                echo json_encode(['status' => 'error', 'message' => 'Accès refusé ou notification introuvable.'], JSON_UNESCAPED_UNICODE);
                http_response_code(403); // Forbidden
                return;
            }

            // Utiliser la méthode du service pour marquer comme lue
            $success = $this->notificationService->marquerNotificationCommeLue($numeroUtilisateur, $notification['id_notification'], $notification['date_reception']);

            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Notification marquée comme lue.']);
                http_response_code(200);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Impossible de marquer la notification comme lue.'], JSON_UNESCAPED_UNICODE);
                http_response_code(500);
            }

        } catch (ElementNonTrouveException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            http_response_code(404);
        } catch (\Exception $e) {
            error_log("Erreur API markAsRead: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erreur interne du serveur: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            http_response_code(500);
        }
    }

    /**
     * API : Marque toutes les notifications non lues de l'utilisateur comme lues.
     */
    public function markAllAsRead(): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $currentUser = $this->authService->getUtilisateurConnecteComplet();
            if (!$currentUser) {
                echo json_encode(['status' => 'error', 'message' => 'Non authentifié.'], JSON_UNESCAPED_UNICODE);
                http_response_code(401);
                return;
            }

            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            // Utiliser une méthode dédiée dans ServiceNotification
            $success = $this->notificationService->marquerToutesNotificationsCommeLues($numeroUtilisateur);

            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Toutes les notifications ont été marquées comme lues.']);
                http_response_code(200);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Impossible de marquer toutes les notifications comme lues.'], JSON_UNESCAPED_UNICODE);
                http_response_code(500);
            }

        } catch (\Exception $e) {
            error_log("Erreur API markAllAsRead: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erreur interne du serveur: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            http_response_code(500);
        }
    }

    /**
     * API : Supprime (archive) une notification spécifique.
     * Attend un seul ID (id_reception) de la route.
     * @param string $idReception L'ID de réception de la notification.
     */
    public function deleteNotification(string $idReception): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $currentUser = $this->authService->getUtilisateurConnecteComplet();
            if (!$currentUser) {
                echo json_encode(['status' => 'error', 'message' => 'Non authentifié.'], JSON_UNESCAPED_UNICODE);
                http_response_code(401);
                return;
            }

            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            // Vérifier que l'ID de réception appartient bien à l'utilisateur connecté
            $notification = $this->notificationService->recevoirModel->trouverParIdentifiant($idReception);
            if (!$notification || $notification['numero_utilisateur'] !== $numeroUtilisateur) {
                echo json_encode(['status' => 'error', 'message' => 'Accès refusé ou notification introuvable.'], JSON_UNESCAPED_UNICODE);
                http_response_code(403); // Forbidden
                return;
            }

            // Utiliser une méthode du service ou directement le modèle pour supprimer
            $pdo = $this->authService->getUtilisateurModel()->getDb();
            $recevoirModel = new \App\Backend\Model\Recevoir($pdo);
            $success = $recevoirModel->supprimerParIdentifiant($idReception);

            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Notification supprimée.']);
                http_response_code(200);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Impossible de supprimer la notification.'], JSON_UNESCAPED_UNICODE);
                http_response_code(500);
            }

        } catch (ElementNonTrouveException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            http_response_code(404);
        } catch (\Exception $e) {
            error_log("Erreur API deleteNotification: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Erreur interne du serveur: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            http_response_code(500);
        }
    }
}