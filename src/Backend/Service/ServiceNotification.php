<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Notification;
use App\Backend\Model\Recevoir;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\EmailServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\ModeleNonTrouveException;

class ServiceNotification implements NotificationServiceInterface
{
    private PDO $pdo;
    private Notification $notificationModel;
    private Recevoir $recevoirModel;
    private Utilisateur $utilisateurModel;
    private GroupeUtilisateur $groupeUtilisateurModel;
    private EmailServiceInterface $emailService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        Notification $notificationModel,
        Recevoir $recevoirModel,
        Utilisateur $utilisateurModel,
        GroupeUtilisateur $groupeUtilisateurModel,
        EmailServiceInterface $emailService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->notificationModel = $notificationModel;
        $this->recevoirModel = $recevoirModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->groupeUtilisateurModel = $groupeUtilisateurModel;
        $this->emailService = $emailService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function envoyerAUtilisateur(string $idUtilisateur, string $idTemplate, array $variables): bool
    {
        $template = $this->notificationModel->trouverUnParCritere(['code_notification' => $idTemplate]);
        if (!$template) {
            throw new ModeleNonTrouveException("Le modèle de notification '{$idTemplate}' n'a pas été trouvé.");
        }

        $idNotification = $this->creerNotificationInstance($template, $variables);

        $this->recevoirModel->creer([
            'id_reception' => $this->identifiantGenerator->generer('RECV'),
            'id_notification' => $idNotification,
            'numero_utilisateur' => $idUtilisateur,
            'est_lu' => false
        ]);

        if ($template['canal_email']) {
            $user = $this->utilisateurModel->trouverParIdentifiant($idUtilisateur);
            if ($user) {
                $this->emailService->envoyerDepuisTemplate($user['email_principal'], $idTemplate, $variables);
            }
        }

        return true;
    }

    public function envoyerAGroupe(string $idGroupe, string $idTemplate, array $variables): bool
    {
        $template = $this->notificationModel->trouverUnParCritere(['code_notification' => $idTemplate]);
        if (!$template) {
            throw new ModeleNonTrouveException("Le modèle de notification '{$idTemplate}' n'a pas été trouvé.");
        }

        $utilisateurs = $this->utilisateurModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupe]);
        if (empty($utilisateurs)) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $idNotification = $this->creerNotificationInstance($template, $variables);
            foreach ($utilisateurs as $user) {
                $this->recevoirModel->creer([
                    'id_reception' => $this->identifiantGenerator->generer('RECV'),
                    'id_notification' => $idNotification,
                    'numero_utilisateur' => $user['numero_utilisateur'],
                    'est_lu' => false
                ]);
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        if ($template['canal_email']) {
            foreach ($utilisateurs as $user) {
                $this->emailService->ajouterEmailALaFile([
                    'destinataire' => $user['email_principal'],
                    'templateCode' => $idTemplate,
                    'variables' => $variables
                ]);
            }
        }

        return true;
    }

    public function listerNotificationsPour(string $idUtilisateur): array
    {
        $sql = "SELECT n.sujet, n.corps_notification, r.id_reception, r.est_lu, r.date_reception
                FROM recevoir r
                JOIN notification n ON r.id_notification = n.id_notification
                WHERE r.numero_utilisateur = :user_id
                ORDER BY r.date_reception DESC
                LIMIT 50";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function compterNotificationsNonLues(string $idUtilisateur): int
    {
        return $this->recevoirModel->compterParCritere(['numero_utilisateur' => $idUtilisateur, 'est_lu' => false]);
    }

    public function marquerToutesCommeLues(string $idUtilisateur): bool
    {
        $sql = "UPDATE recevoir SET est_lu = 1, date_lecture = :date_lecture WHERE numero_utilisateur = :user_id AND est_lu = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $idUtilisateur, ':date_lecture' => (new \DateTime())->format('Y-m-d H:i:s')]);
    }

    public function supprimerNotification(string $idReception): bool
    {
        return $this->recevoirModel->supprimerParIdentifiant($idReception);
    }

    private function creerNotificationInstance(array $template, array $variables): string
    {
        $sujet = $template['sujet'];
        $corps = $template['corps_notification'];

        foreach ($variables as $key => $value) {
            $placeholder = "{{{$key}}}";
            $sujet = str_replace($placeholder, (string) $value, $sujet);
            $corps = str_replace($placeholder, (string) $value, $corps);
        }

        $idNotification = $this->identifiantGenerator->generer('NOTIF');
        $this->notificationModel->creer([
            'id_notification' => $idNotification,
            'sujet' => $sujet,
            'corps_notification' => $corps,
            'code_notification' => $template['code_notification']
        ]);

        return $idNotification;
    }
}