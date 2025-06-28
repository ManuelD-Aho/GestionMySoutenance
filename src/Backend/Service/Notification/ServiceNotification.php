<?php

namespace App\Backend\Service\Notification;

use PDO;
use App\Backend\Model\Notification;
use App\Backend\Model\Recevoir;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Email\ServiceEmailInterface;
use App\Backend\Service\NotificationConfiguration\ServiceNotificationConfigurationInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceNotification implements ServiceNotificationInterface
{
    private Notification $notificationModel;
    public Recevoir $recevoirModel;
    private Utilisateur $utilisateurModel;
    private GroupeUtilisateur $groupeUtilisateurModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private ServiceEmailInterface $emailService;
    private ServiceNotificationConfigurationInterface $configNotificationService;

    public function __construct(
        PDO $db,
        Notification $notificationModel,
        Recevoir $recevoirModel,
        Utilisateur $utilisateurModel,
        GroupeUtilisateur $groupeUtilisateurModel,
        ServiceSupervisionAdminInterface $supervisionService,
        ServiceEmailInterface $emailService,
        ServiceNotificationConfigurationInterface $configNotificationService
    ) {
        $this->notificationModel = $notificationModel;
        $this->recevoirModel = $recevoirModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->groupeUtilisateurModel = $groupeUtilisateurModel;
        $this->supervisionService = $supervisionService;
        $this->emailService = $emailService;
        $this->configNotificationService = $configNotificationService;
    }

    public function envoyerNotificationUtilisateur(string $numeroUtilisateur, string $idNotificationTemplate, string $messageOverride = ''): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$utilisateur) {
            throw new ElementNonTrouveException("Utilisateur destinataire {$numeroUtilisateur} non trouvé pour la notification.");
        }

        $template = $this->notificationModel->trouverParIdentifiant($idNotificationTemplate);
        if (!$template) {
            throw new ElementNonTrouveException("Modèle de notification '{$idNotificationTemplate}' non trouvé.");
        }

        $this->recevoirModel->commencerTransaction();
        try {
            $success = $this->recevoirModel->creer([
                'numero_utilisateur' => $numeroUtilisateur,
                'id_notification' => $idNotificationTemplate,
                'date_reception' => date('Y-m-d H:i:s'),
                'lue' => 0,
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de l'enregistrement de la notification pour {$numeroUtilisateur}.");
            }

            $this->recevoirModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'NOTIFICATION_ENVOYEE',
                "Notification '{$idNotificationTemplate}' envoyée à {$numeroUtilisateur}" . ($messageOverride ? " (Msg: '{$messageOverride}')" : ''),
                $numeroUtilisateur,
                'Utilisateur'
            );
            return true;
        } catch (\Exception $e) {
            $this->recevoirModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_NOTIFICATION_ENVOYEE',
                "Erreur envoi notification à {$numeroUtilisateur}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function envoyerNotificationGroupe(string $idGroupeUtilisateur, string $idNotificationTemplate, string $messageOverride = ''): bool
    {
        $groupe = $this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur);
        if (!$groupe) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé pour la notification.");
        }

        $membresDuGroupe = $this->utilisateurModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur, 'statut_compte' => 'actif']);
        if (empty($membresDuGroupe)) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'NOTIFICATION_GROUPE_AUCUN_DEST',
                "Notification groupe '{$idGroupeUtilisateur}' non envoyée : aucun membre actif."
            );
            return false;
        }

        $countSent = 0;
        foreach ($membresDuGroupe as $membre) {
            try {
                if ($this->envoyerNotificationUtilisateur($membre['numero_utilisateur'], $idNotificationTemplate, $messageOverride)) {
                    $countSent++;
                }
            } catch (\Exception $e) {
                error_log("Échec d'envoi de notification à {$membre['numero_utilisateur']} dans le groupe {$idGroupeUtilisateur}: " . $e->getMessage());
            }
        }
        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'NOTIFICATION_GROUPE_ENVOYEE',
            "Notification '{$idNotificationTemplate}' envoyée à {$countSent} membres du groupe '{$idGroupeUtilisateur}'."
        );
        return $countSent > 0;
    }

    public function recupererNotificationsUtilisateur(string $numeroUtilisateur, bool $inclureLues = false): array
    {
        $criteres = ['numero_utilisateur' => $numeroUtilisateur];
        if (!$inclureLues) {
            $criteres['lue'] = 0;
        }

        $receptions = $this->recevoirModel->trouverParCritere($criteres, ['id_reception', 'id_notification', 'date_reception', 'lue']);

        $notificationsCompletes = [];
        foreach ($receptions as $rec) {
            $template = $this->notificationModel->trouverParIdentifiant($rec['id_notification']);
            if ($template) {
                $notification = array_merge($rec, [
                    'libelle_notification_template' => $template['libelle_notification'],
                ]);
                $notificationsCompletes[] = $notification;
            }
        }
        return $notificationsCompletes;
    }

    public function marquerNotificationCommeLue(string $idReception): bool
    {
        $reception = $this->recevoirModel->trouverParIdentifiant($idReception);
        if (!$reception) {
            throw new ElementNonTrouveException("Réception de notification non trouvée.");
        }
        if ($reception['lue'] == 1) {
            return true;
        }

        $success = $this->recevoirModel->mettreAJourParIdentifiant(
            $idReception,
            ['lue' => 1, 'date_lecture' => date('Y-m-d H:i:s')]
        );

        if ($success) {
            $this->supervisionService->enregistrerAction(
                $reception['numero_utilisateur'],
                'MARQUER_NOTIFICATION_LUE',
                "Notification '{$reception['id_notification']}' marquée comme lue.",
                $idReception,
                'Recevoir'
            );
        }
        return $success;
    }

    public function compterNotificationsNonLues(string $numeroUtilisateur): int
    {
        return $this->recevoirModel->compterParCritere(['numero_utilisateur' => $numeroUtilisateur, 'lue' => 0]);
    }

    public function archiverAnciennesNotificationsLues(string $numeroUtilisateur, int $joursAnciennete = 30): int
    {
        $dateLimite = (new \DateTime())->modify("-{$joursAnciennete} days")->format('Y-m-d H:i:s');
        $criteres = [
            'numero_utilisateur' => $numeroUtilisateur,
            'lue' => 1,
            'date_reception' => ['operator' => '<', 'value' => $dateLimite]
        ];

        $notificationsToArchive = $this->recevoirModel->trouverParCritere($criteres, ['id_reception']);
        $countArchived = 0;

        foreach ($notificationsToArchive as $notif) {
            try {
                if ($this->recevoirModel->supprimerParIdentifiant($notif['id_reception'])) {
                    $countArchived++;
                }
            } catch (\Exception $e) {
                error_log("Erreur archivage notification {$notif['id_reception']} pour {$numeroUtilisateur}: " . $e->getMessage());
            }
        }

        if ($countArchived > 0) {
            $this->supervisionService->enregistrerAction(
                $numeroUtilisateur,
                'ARCHIVAGE_NOTIFICATIONS',
                "{$countArchived} notifications lues de plus de {$joursAnciennete} jours archivées."
            );
        }
        return $countArchived;
    }
}