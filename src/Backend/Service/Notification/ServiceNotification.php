<?php
namespace App\Backend\Service\Notification;

use PDO;
use App\Backend\Model\Notification;
use App\Backend\Model\Recevoir;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceNotification implements ServiceNotificationInterface
{
    private Notification $notificationModel;
    private Recevoir $recevoirModel;
    private Utilisateur $utilisateurModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(PDO $db, ServiceSupervisionAdminInterface $supervisionService, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->notificationModel = new Notification($db);
        $this->recevoirModel = new Recevoir($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function send(string $numeroUtilisateur, string $templateCode, array $variables = []): bool
    {
        if (!$this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur)) {
            throw new ElementNonTrouveException("Utilisateur destinataire {$numeroUtilisateur} non trouvé.");
        }
        $template = $this->notificationModel->trouverParIdentifiant($templateCode);
        if (!$template) {
            throw new ElementNonTrouveException("Modèle de notification '{$templateCode}' non trouvé.");
        }

        $idReception = $this->idGenerator->generate('reception_notification');
        $data = [
            'id_reception' => $idReception,
            'numero_utilisateur' => $numeroUtilisateur,
            'id_notification' => $templateCode,
            'date_reception' => date('Y-m-d H:i:s'),
            'lue' => 0,
        ];

        if (!$this->recevoirModel->creer($data)) {
            throw new OperationImpossibleException("Échec de l'enregistrement de la notification pour {$numeroUtilisateur}.");
        }
        return true;
    }

    public function sendToGroup(string $idGroupeUtilisateur, string $templateCode, array $variables = []): bool
    {
        $membres = $this->utilisateurModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur, 'statut_compte' => 'actif']);
        if (empty($membres)) return false;

        $countSent = 0;
        foreach ($membres as $membre) {
            if ($this->send($membre['numero_utilisateur'], $templateCode, $variables)) {
                $countSent++;
            }
        }
        return $countSent > 0;
    }

    public function getUserNotifications(string $numeroUtilisateur, bool $includeRead = false, int $limit = 20): array
    {
        $criteres = ['numero_utilisateur' => $numeroUtilisateur];
        if (!$includeRead) {
            $criteres['lue'] = 0;
        }
        return $this->recevoirModel->trouverParCritere($criteres, ['*'], 'AND', 'date_reception DESC', $limit);
    }

    public function markAsRead(string $idReception): bool
    {
        $reception = $this->recevoirModel->trouverParIdentifiant($idReception);
        if (!$reception) {
            throw new ElementNonTrouveException("Notification non trouvée.");
        }
        if ($reception['lue']) return true;

        return $this->recevoirModel->mettreAJourParIdentifiant($idReception, ['lue' => 1, 'date_lecture' => date('Y-m-d H:i:s')]);
    }

    public function countUnread(string $numeroUtilisateur): int
    {
        return $this->recevoirModel->compterParCritere(['numero_utilisateur' => $numeroUtilisateur, 'lue' => 0]);
    }
}