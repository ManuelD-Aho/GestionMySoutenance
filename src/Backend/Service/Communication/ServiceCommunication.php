<?php
// src/Backend/Service/Communication/ServiceCommunication.php

namespace App\Backend\Service\Communication;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Backend\Model\GenericModel;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, EmailException};

class ServiceCommunication implements ServiceCommunicationInterface
{
    private PDO $db;
    private GenericModel $notificationModel;
    private GenericModel $recevoirModel;
    private GenericModel $conversationModel;
    private GenericModel $messageChatModel;
    private GenericModel $participantConversationModel;
    private GenericModel $matriceNotificationModel;
    private Utilisateur $utilisateurModel;
    private ServiceSystemeInterface $systemeService;
    private ServiceSupervisionInterface $supervisionService;

    public function __construct(
        PDO $db,
        GenericModel $notificationModel,
        GenericModel $recevoirModel,
        GenericModel $conversationModel,
        GenericModel $messageChatModel,
        GenericModel $participantConversationModel,
        GenericModel $matriceNotificationModel,
        Utilisateur $utilisateurModel,
        ServiceSystemeInterface $systemeService,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->notificationModel = $notificationModel;
        $this->recevoirModel = $recevoirModel;
        $this->conversationModel = $conversationModel;
        $this->messageChatModel = $messageChatModel;
        $this->participantConversationModel = $participantConversationModel;
        $this->matriceNotificationModel = $matriceNotificationModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->systemeService = $systemeService;
        $this->supervisionService = $supervisionService;
    }

    // --- Section 1: Envoi de Messages ---

    public function envoyerNotificationInterne(string $numeroUtilisateur, string $idNotificationTemplate, array $variables = []): bool
    {
        if (!$this->notificationModel->trouverParIdentifiant($idNotificationTemplate)) {
            throw new ElementNonTrouveException("Modèle de notification '{$idNotificationTemplate}' non trouvé.");
        }

        $idReception = $this->systemeService->genererIdentifiantUnique('RECEP');

        return (bool) $this->recevoirModel->creer([
            'id_reception' => $idReception,
            'numero_utilisateur' => $numeroUtilisateur,
            'id_notification' => $idNotificationTemplate,
            'variables_contenu' => !empty($variables) ? json_encode($variables) : null,
            'date_reception' => date('Y-m-d H:i:s'),
            'lue' => 0
        ]);
    }

    public function envoyerNotificationGroupe(string $idGroupeUtilisateur, string $idNotificationTemplate, array $variables = []): bool
    {
        $membres = $this->utilisateurModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur, 'statut_compte' => 'actif']);
        if (empty($membres)) return false;

        $succesCount = 0;
        foreach ($membres as $membre) {
            if ($this->envoyerNotificationInterne($membre['numero_utilisateur'], $idNotificationTemplate, $variables)) {
                $succesCount++;
            }
        }
        return $succesCount > 0;
    }

    public function envoyerEmail(string $destinataireEmail, string $idNotificationTemplate, array $variables = [], array $piecesJointes = []): bool
    {
        $utilisateur = $this->utilisateurModel->trouverUnParCritere(['email_principal' => $destinataireEmail]);
        if ($utilisateur) {
            $preferences = json_decode($utilisateur['preferences_notifications'] ?? '[]', true);
            if (isset($preferences[$idNotificationTemplate]['email']) && $preferences[$idNotificationTemplate]['email'] === false) {
                return true;
            }
        }

        $template = $this->notificationModel->trouverParIdentifiant($idNotificationTemplate);
        if (!$template) throw new ElementNonTrouveException("Modèle d'email '{$idNotificationTemplate}' non trouvé.");

        $sujet = $this->personnaliserMessage($template['libelle_notification'], $variables);

        $corpsMessage = $this->personnaliserMessage($template['contenu'], $variables);
        $layoutPath = __DIR__ . '/../../../templates/email/layout_email_generique.html';
        if (file_exists($layoutPath)) {
            $corpsFinal = file_get_contents($layoutPath);
            $corpsFinal = str_replace('{{contenu_principal}}', $corpsMessage, $corpsFinal);
            $corpsFinal = str_replace('{{sujet_email}}', $sujet, $corpsFinal);
            $corpsFinal = str_replace('{{annee_courante}}', date('Y'), $corpsFinal);
            $corpsFinal = str_replace('{{app_url}}', $_ENV['APP_URL'], $corpsFinal);
        } else {
            $corpsFinal = $corpsMessage;
        }

        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = $this->systemeService->getParametre('SMTP_HOST');
            $mailer->SMTPAuth = (bool) $this->systemeService->getParametre('SMTP_AUTH', true);
            $mailer->Username = $this->systemeService->getParametre('SMTP_USER');
            $mailer->Password = $this->systemeService->getParametre('SMTP_PASS');
            $mailer->SMTPSecure = $this->systemeService->getParametre('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS);
            $mailer->Port = (int) $this->systemeService->getParametre('SMTP_PORT', 587);

            $mailer->setFrom($this->systemeService->getParametre('SMTP_FROM_EMAIL'), $this->systemeService->getParametre('SMTP_FROM_NAME'));
            $mailer->addAddress($destinataireEmail);
            $mailer->isHTML(true);
            $mailer->CharSet = 'UTF-8';
            $mailer->Subject = $sujet;
            $mailer->Body = $corpsFinal;
            $mailer->AltBody = strip_tags($corpsFinal);

            foreach ($piecesJointes as $pj) {
                $mailer->addAttachment($pj['path'], $pj['name']);
            }

            $mailer->send();
            // Correction: Ordre des arguments et type de $detailsJson
            $this->supervisionService->enregistrerAction(
                'SYSTEM', // numeroUtilisateur
                'ENVOI_EMAIL_SUCCES', // idAction
                null, // idEntiteConcernee
                'Email', // typeEntiteConcernee (type de l'entité, pas l'email lui-même)
                ['destinataire' => $destinataireEmail, 'template' => $idNotificationTemplate] // detailsJson (doit être un tableau)
            );
            return true;
        } catch (PHPMailerException $e) {
            // Correction: Ordre des arguments et type de $detailsJson
            $this->supervisionService->enregistrerAction(
                'SYSTEM', // numeroUtilisateur
                'ENVOI_EMAIL_ECHEC', // idAction
                null, // idEntiteConcernee
                'Email', // typeEntiteConcernee
                ['destinataire' => $destinataireEmail, 'error' => $e->errorMessage(), 'template' => $idNotificationTemplate] // detailsJson
            );
            throw new EmailException("Erreur PHPMailer : " . $e->errorMessage());
            // Correction: Retourner false pour respecter la signature de la méthode
            return false;
        }
    }


    // ====================================================================
    // SECTION 2: Messagerie Instantanée
    // ====================================================================

    public function demarrerConversation(array $participantsIds, ?string $nomConversation = null): string
    {
        if (count($participantsIds) < 2) throw new OperationImpossibleException("Une conversation doit avoir au moins 2 participants.");

        $type = count($participantsIds) > 2 ? 'Groupe' : 'Direct';
        $idConversation = $this->systemeService->genererIdentifiantUnique('CONV');

        $this->db->beginTransaction();
        try {
            $this->conversationModel->creer([
                'id_conversation' => $idConversation,
                'nom_conversation' => $nomConversation,
                'type_conversation' => $type
            ]);
            foreach ($participantsIds as $userId) {
                $this->participantConversationModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $userId]);
            }
            $this->db->commit();
            return $idConversation;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function envoyerMessageChat(string $idConversation, string $expediteurId, string $contenu, ?array $pieceJointe = null): string
    {
        $idMessage = $this->systemeService->genererIdentifiantUnique('MSG');
        $this->messageChatModel->creer([
            'id_message_chat' => $idMessage,
            'id_conversation' => $idConversation,
            'numero_utilisateur_expediteur' => $expediteurId,
            'contenu_message' => $contenu,
            'piece_jointe_path' => $pieceJointe['path'] ?? null,
            'piece_jointe_nom' => $pieceJointe['name'] ?? null
        ]);
        return $idMessage;
    }
    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array { /* ... */ }
    public function listerMessagesPourConversation(string $idConversation): array { /* ... */ }

    // --- Section 3: Consultation ---

    public function listerNotificationsNonLues(string $numeroUtilisateur): array
    {
        // Requête enrichie pour obtenir le libellé du template
        $sql = "SELECT r.*, n.libelle_notification 
                FROM recevoir r
                JOIN notification n ON r.id_notification = n.id_notification
                WHERE r.numero_utilisateur = :user_id AND r.lue = 0
                ORDER BY r.date_reception DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $numeroUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marquerNotificationLue(string $idReception): bool
    {
        return $this->recevoirModel->mettreAJourParIdentifiant($idReception, ['lue' => 1, 'date_lecture' => date('Y-m-d H:i:s')]);
    }
    public function listerModelesNotification(): array
    {
        return $this->notificationModel->trouverTout();
    }

    public function mettreAJourModeleNotification(string $id, string $libelle, string $contenu): bool
    {
        return $this->notificationModel->mettreAJourParIdentifiant($id, ['libelle_notification' => $libelle, 'contenu' => $contenu]);
    }

    public function listerReglesMatrice(): array
    {
        // Requête enrichie pour l'affichage
        $sql = "SELECT m.*, a.libelle_action, g.libelle_groupe 
                FROM matrice_notification_regles m
                JOIN action a ON m.id_action_declencheur = a.id_action
                JOIN groupe_utilisateur g ON m.id_groupe_destinataire = g.id_groupe_utilisateur";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mettreAJourRegleMatrice(string $idRegle, string $canal, bool $estActive): bool
    {
        return $this->matriceNotificationModel->mettreAJourParIdentifiant($idRegle, [
            'canal_notification' => $canal,
            'est_active' => $estActive ? 1 : 0
        ]);
    }

    public function archiverConversationsInactives(int $jours): int
    {
        $dateLimite = (new \DateTime())->modify("-{$jours} days")->format('Y-m-d H:i:s');
        $sql = "UPDATE conversation c
                LEFT JOIN (
                    SELECT id_conversation, MAX(date_envoi) as last_message_date
                    FROM message_chat
                    GROUP BY id_conversation
                ) mc ON c.id_conversation = mc.id_conversation
                SET c.statut = 'Archivée'
                WHERE (mc.last_message_date IS NULL AND c.date_creation_conv < :date_limite)
                   OR (mc.last_message_date < :date_limite AND c.statut != 'Archivée')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date_limite' => $dateLimite]);
        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            $this->supervisionService->enregistrerAction('SYSTEM', 'ARCHIVAGE_CONVERSATIONS', null, 'Conversation', ['count' => $rowCount, 'days_inactive' => $jours]);
        }
        return $rowCount;
    }

    // --- Méthode privée ---
    private function personnaliserMessage(string $message, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $message = str_replace("{{{$key}}}", htmlspecialchars((string)$value), $message);
        }
        return $message;
    }
}