<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Conversation;
use App\Backend\Model\MessageChat;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\LectureMessage;
use App\Backend\Service\Interface\MessagerieServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\PermissionException;

class ServiceMessagerie implements MessagerieServiceInterface
{
    private PDO $pdo;
    private Conversation $conversationModel;
    private MessageChat $messageChatModel;
    private ParticipantConversation $participantModel;
    private LectureMessage $lectureMessageModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        Conversation $conversationModel,
        MessageChat $messageChatModel,
        ParticipantConversation $participantModel,
        LectureMessage $lectureMessageModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->conversationModel = $conversationModel;
        $this->messageChatModel = $messageChatModel;
        $this->participantModel = $participantModel;
        $this->lectureMessageModel = $lectureMessageModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function creerConversation(array $participants, ?string $nomGroupe): string
    {
        $idConversation = $this->identifiantGenerator->generer('CONV');
        $isGroup = count($participants) > 2 || !empty($nomGroupe);

        $this->pdo->beginTransaction();
        try {
            $this->conversationModel->creer([
                'id_conversation' => $idConversation,
                'nom_conversation' => $nomGroupe,
                'date_creation' => (new \DateTime())->format('Y-m-d H:i:s'),
                'est_groupe' => $isGroup
            ]);

            foreach ($participants as $participantId) {
                $this->participantModel->creer([
                    'id_conversation' => $idConversation,
                    'numero_utilisateur' => $participantId,
                    'date_ajout' => (new \DateTime())->format('Y-m-d H:i:s')
                ]);
            }

            $this->auditService->enregistrerAction(reset($participants), 'CONVERSATION_CREATED', $idConversation, 'Conversation', ['participants' => $participants]);
            $this->pdo->commit();

            return $idConversation;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function envoyerMessage(string $idConversation, string $idExpediteur, string $contenu): string
    {
        if (!$this->participantModel->trouverUnParCritere(['id_conversation' => $idConversation, 'numero_utilisateur' => $idExpediteur])) {
            throw new PermissionException("L'expéditeur ne fait pas partie de cette conversation.");
        }

        $idMessage = $this->identifiantGenerator->generer('MSG');
        $this->messageChatModel->creer([
            'id_message_chat' => $idMessage,
            'id_conversation' => $idConversation,
            'numero_utilisateur_expediteur' => $idExpediteur,
            'contenu_message' => $contenu,
            'date_envoi' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        $participants = $this->participantModel->trouverParCritere(['id_conversation' => $idConversation]);
        foreach ($participants as $participant) {
            if ($participant['numero_utilisateur'] !== $idExpediteur) {
                $this->notificationService->envoyerAUtilisateur($participant['numero_utilisateur'], 'NEW_MESSAGE_TPL', ['sender' => $idExpediteur, 'conversation' => $idConversation]);
            }
        }

        return $idMessage;
    }

    public function listerConversationsPour(string $idUtilisateur): array
    {
        $sql = "SELECT c.*, m.contenu_message as dernier_message, m.date_envoi as date_dernier_message
                FROM conversation c
                JOIN participant_conversation pc ON c.id_conversation = pc.id_conversation
                LEFT JOIN message_chat m ON c.dernier_message_id = m.id_message_chat
                WHERE pc.numero_utilisateur = :user_id
                ORDER BY m.date_envoi DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessagesDeConversation(string $idConversation, array $options = []): array
    {
        $limit = $options['limit'] ?? 50;
        $offset = $options['offset'] ?? 0;
        return $this->messageChatModel->trouverParCritere(
            ['id_conversation' => $idConversation],
            ['*'],
            'AND',
            'date_envoi DESC',
            $limit,
            $offset
        );
    }

    public function marquerCommeLu(string $idMessage, string $idUtilisateur): bool
    {
        if ($this->lectureMessageModel->trouverUnParCritere(['id_message_chat' => $idMessage, 'numero_utilisateur' => $idUtilisateur])) {
            return true;
        }
        return (bool)$this->lectureMessageModel->creer([
            'id_message_chat' => $idMessage,
            'numero_utilisateur' => $idUtilisateur,
            'date_lecture' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    public function ajouterParticipantAGroupe(string $idConversation, string $idUtilisateur): bool
    {
        $conversation = $this->conversationModel->trouverParIdentifiant($idConversation);
        if (!$conversation || !$conversation['est_groupe']) {
            throw new OperationImpossibleException("Cette opération n'est possible que pour les conversations de groupe.");
        }
        return (bool)$this->participantModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $idUtilisateur]);
    }

    public function retirerParticipantDeGroupe(string $idConversation, string $idUtilisateur): bool
    {
        return $this->participantModel->supprimerParCles(['id_conversation' => $idConversation, 'numero_utilisateur' => $idUtilisateur]);
    }

    public function archiverConversation(string $idConversation, string $idUtilisateur): bool
    {
        return $this->participantModel->mettreAJourParCles(
            ['id_conversation' => $idConversation, 'numero_utilisateur' => $idUtilisateur],
            ['est_archive' => true]
        );
    }
}