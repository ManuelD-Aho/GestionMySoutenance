<?php
namespace App\Backend\Service\Notification;

use PDO;
use App\Backend\Model\Notification;
use App\Backend\Model\Recevoir;
use App\Backend\Model\Utilisateur; // Pour trouver l'utilisateur et son type/groupe
use App\Backend\Model\GroupeUtilisateur; // Pour trouver les utilisateurs d'un groupe
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceNotification implements ServiceNotificationInterface
{
    private Notification $notificationModel;
    private Recevoir $recevoirModel;
    private Utilisateur $utilisateurModel;
    private GroupeUtilisateur $groupeUtilisateurModel;
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(PDO $db, ServiceSupervisionAdmin $supervisionService)
    {
        $this->notificationModel = new Notification($db);
        $this->recevoirModel = new Recevoir($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->groupeUtilisateurModel = new GroupeUtilisateur($db);
        $this->supervisionService = $supervisionService;
    }

    /**
     * Envoie une notification à un utilisateur spécifique.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur destinataire (VARCHAR).
     * @param string $idNotificationTemplate L'ID du modèle de notification à utiliser (VARCHAR).
     * @param string $messageOverride Message personnalisé qui remplace le contenu du template si fourni.
     * @return bool Vrai si la notification a été envoyée.
     * @throws ElementNonTrouveException Si l'utilisateur ou le modèle de notification n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'échec de l'enregistrement de la réception.
     */
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
                'id_notification' => $idNotificationTemplate, // Ou un ID unique si chaque réception est une entrée unique. Ici, c'est l'ID du template.
                'date_reception' => date('Y-m-d H:i:s'),
                'lue' => 0, // Par défaut non lue
                // Utiliser un champ 'message_personnalise' dans la table 'recevoir' si $messageOverride est utilisé.
                // 'message_personnalise' => $messageOverride // A ajouter dans la table 'recevoir' si besoin.
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

    /**
     * Envoie une notification à tous les utilisateurs d'un groupe spécifique.
     * @param string $idGroupeUtilisateur L'ID du groupe destinataire (VARCHAR).
     * @param string $idNotificationTemplate L'ID du modèle de notification à utiliser (VARCHAR).
     * @param string $messageOverride Message personnalisé qui remplace le contenu du template si fourni.
     * @return bool Vrai si au moins une notification a été envoyée.
     * @throws ElementNonTrouveException Si le groupe ou le modèle n'est pas trouvé.
     */
    public function envoyerNotificationGroupe(string $idGroupeUtilisateur, string $idNotificationTemplate, string $messageOverride = ''): bool
    {
        $groupe = $this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur);
        if (!$groupe) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé pour la notification.");
        }
        $template = $this->notificationModel->trouverParIdentifiant($idNotificationTemplate);
        if (!$template) {
            throw new ElementNonTrouveException("Modèle de notification '{$idNotificationTemplate}' non trouvé.");
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
                // Continuer même si un envoi échoue
            }
        }
        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'NOTIFICATION_GROUPE_ENVOYEE',
            "Notification '{$idNotificationTemplate}' envoyée à {$countSent} membres du groupe '{$idGroupeUtilisateur}'."
        );
        return $countSent > 0;
    }

    /**
     * Récupère toutes les notifications d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param bool $inclureLues Indique si les notifications lues doivent être incluses.
     * @return array Liste des notifications (avec les détails du template).
     */
    public function recupererNotificationsUtilisateur(string $numeroUtilisateur, bool $inclureLues = false): array
    {
        $criteres = ['numero_utilisateur' => $numeroUtilisateur];
        if (!$inclureLues) {
            $criteres['lue'] = 0;
        }

        $receptions = $this->recevoirModel->trouverParCritere($criteres, ['id_notification', 'date_reception', 'lue']);

        $notificationsCompletes = [];
        foreach ($receptions as $rec) {
            $template = $this->notificationModel->trouverParIdentifiant($rec['id_notification']);
            if ($template) {
                $notification = array_merge($rec, [
                    'libelle_notification_template' => $template['libelle_notification'],
                    // 'contenu_template' => $template['contenu'] // Si le champ contenu est dans la table Notification
                ]);
                $notificationsCompletes[] = $notification;
            }
        }
        return $notificationsCompletes;
    }

    /**
     * Marque une notification spécifique comme lue pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idNotificationTemplate L'ID du modèle de notification.
     * @param string $dateReception La date exacte de réception de la notification à marquer (clé composée).
     * @return bool Vrai si la notification a été marquée comme lue.
     * @throws ElementNonTrouveException Si la réception de notification n'est pas trouvée.
     */
    public function marquerNotificationCommeLue(string $numeroUtilisateur, string $idNotificationTemplate, string $dateReception): bool
    {
        $reception = $this->recevoirModel->trouverReceptionParCles($numeroUtilisateur, $idNotificationTemplate, $dateReception);
        if (!$reception) {
            throw new ElementNonTrouveException("Réception de notification non trouvée.");
        }
        if ($reception['lue'] === 1) { // Déjà lue
            return true;
        }

        $success = $this->recevoirModel->mettreAJourReceptionParCles(
            $numeroUtilisateur,
            $idNotificationTemplate,
            $dateReception,
            ['lue' => 1, 'date_lecture' => date('Y-m-d H:i:s')]
        );

        if ($success) {
            $this->supervisionService->enregistrerAction(
                $numeroUtilisateur,
                'MARQUER_NOTIFICATION_LUE',
                "Notification '{$idNotificationTemplate}' marquée comme lue.",
                $idNotificationTemplate,
                'Notification'
            );
        }
        return $success;
    }

    /**
     * Compte le nombre de notifications non lues pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return int Le nombre de notifications non lues.
     */
    public function compterNotificationsNonLues(string $numeroUtilisateur): int
    {
        return $this->recevoirModel->compterParCritere(['numero_utilisateur' => $numeroUtilisateur, 'lue' => 0]);
    }

    /**
     * Archive (supprime) les anciennes notifications lues pour un utilisateur.
     * Utile pour la maintenance.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param int $joursAnciennete Le nombre de jours au-delà desquels archiver les notifications.
     * @return int Le nombre de notifications archivées.
     */
    public function archiverAnciennesNotificationsLues(string $numeroUtilisateur, int $joursAnciennete = 30): int
    {
        $dateLimite = (new \DateTime())->modify("-{$joursAnciennete} days")->format('Y-m-d H:i:s');
        $criteres = [
            'numero_utilisateur' => $numeroUtilisateur,
            'lue' => 1,
            'date_reception' => ['operator' => '<', 'value' => $dateLimite]
        ];

        $notificationsToArchive = $this->recevoirModel->trouverParCritere($criteres, ['numero_utilisateur', 'id_notification', 'date_reception']);
        $countArchived = 0;

        foreach ($notificationsToArchive as $notif) {
            try {
                if ($this->recevoirModel->supprimerParClesInternes([
                    'numero_utilisateur' => $notif['numero_utilisateur'],
                    'id_notification' => $notif['id_notification'],
                    'date_reception' => $notif['date_reception']
                ])) {
                    $countArchived++;
                }
            } catch (\Exception $e) {
                error_log("Erreur archivage notification {$notif['id_notification']} pour {$numeroUtilisateur}: " . $e->getMessage());
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