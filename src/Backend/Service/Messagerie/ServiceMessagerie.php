<?php

namespace App\Backend\Service\Messagerie;

use PDO;
use App\Backend\Model\Conversation;
use App\Backend\Model\MessageChat;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\LectureMessage;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceMessagerie implements ServiceMessagerieInterface
{
    private Conversation $conversationModel;
    private MessageChat $messageChatModel;
    private ParticipantConversation $participantConversationModel;
    private LectureMessage $lectureMessageModel;
    private Utilisateur $utilisateurModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        Conversation $conversationModel,
        MessageChat $messageChatModel,
        ParticipantConversation $participantConversationModel,
        LectureMessage $lectureMessageModel,
        Utilisateur $utilisateurModel,
        ServiceNotificationInterface $notificationService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->conversationModel = $conversationModel;
        $this->messageChatModel = $messageChatModel;
        $this->participantConversationModel = $participantConversationModel;
        $this->lectureMessageModel = $lectureMessageModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function demarrerOuRecupererConversationDirecte(string $numeroUtilisateur1, string $numeroUtilisateur2): string
    {
        if (!$this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur1) || !$this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur2)) {
            throw new ElementNonTrouveException("Un ou plusieurs utilisateurs n'existent pas.");
        }

        $conversationsUser1 = $this->participantConversationModel->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur1], ['id_conversation']);
        $conversationsUser2 = $this->participantConversationModel->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur2], ['id_conversation']);

        $commonConversations = array_intersect(array_column($conversationsUser1, 'id_conversation'), array_column($conversationsUser2, 'id_conversation'));

        foreach ($commonConversations as $convId) {
            $conv = $this->conversationModel->trouverParIdentifiant($convId);
            if ($conv && $conv['type_conversation'] === 'Direct') {
                $participants = $this->participantConversationModel->compterParCritere(['id_conversation' => $convId]);
                if ($participants === 2) {
                    return $convId;
                }
            }
        }

        $this->conversationModel->commencerTransaction();
        try {
            $idConversation = $this->idGenerator->genererIdentifiantUnique('CONV');
            $conversationName = "Conversation entre " . $numeroUtilisateur1 . " et " . $numeroUtilisateur2;

            if (!$this->conversationModel->creer([
                'id_conversation' => $idConversation,
                'nom_conversation' => $conversationName,
                'type_conversation' => 'Direct'
            ])) {
                throw new OperationImpossibleException("Échec de la création de la conversation.");
            }

            if (!$this->participantConversationModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroUtilisateur1])) {
                throw new OperationImpossibleException("Échec d'ajout du participant 1.");
            }
            if (!$this->participantConversationModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroUtilisateur2])) {
                throw new OperationImpossibleException("Échec d'ajout du participant 2.");
            }

            $this->conversationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_CONVERSATION_DIRECTE',
                "Conversation directe créée entre {$numeroUtilisateur1} et {$numeroUtilisateur2}",
                $idConversation,
                'Conversation'
            );
            return $idConversation;
        } catch (\Exception $e) {
            $this->conversationModel->annulerTransaction();
            throw $e;
        }
    }

    public function creerNouvelleConversationDeGroupe(string $nomConversation, string $numeroCreateur, array $numerosParticipants): string
    {
        $this->conversationModel->commencerTransaction();
        try {
            $idConversation = $this->idGenerator->genererIdentifiantUnique('CONV');

            $data = [
                'id_conversation' => $idConversation,
                'nom_conversation' => $nomConversation,
                'type_conversation' => 'Groupe'
            ];

            if (!$this->conversationModel->creer($data)) {
                throw new OperationImpossibleException("Échec de la création de la conversation de groupe.");
            }

            if (!$this->participantConversationModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroCreateur])) {
                throw new OperationImpossibleException("Échec d'ajout du créateur à la conversation.");
            }

            foreach ($numerosParticipants as $numParticipant) {
                if (!$this->utilisateurModel->trouverParIdentifiant($numParticipant)) {
                    throw new ElementNonTrouveException("Participant {$numParticipant} non trouvé.");
                }
                if (!$this->participantConversationModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numParticipant])) {
                    throw new OperationImpossibleException("Échec d'ajout du participant {$numParticipant} à la conversation.");
                }
            }

            $this->conversationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroCreateur,
                'CREATION_CONVERSATION_GROUPE',
                "Conversation de groupe '{$nomConversation}' créée (ID: {$idConversation})",
                $idConversation,
                'Conversation'
            );
            return $idConversation;
        } catch (\Exception $e) {
            $this->conversationModel->annulerTransaction();
            throw $e;
        }
    }

    public function envoyerMessageDansConversation(string $idConversation, string $numeroExpediteur, string $contenuMessage): string
    {
        if (!$this->conversationModel->trouverParIdentifiant($idConversation)) {
            throw new ElementNonTrouveException("Conversation non trouvée.");
        }
        if (!$this->utilisateurModel->trouverParIdentifiant($numeroExpediteur)) {
            throw new ElementNonTrouveException("Expéditeur non trouvé.");
        }
        if (!$this->participantConversationModel->trouverParticipantParCles($idConversation, $numeroExpediteur)) {
            throw new OperationImpossibleException("L'expéditeur n'est pas un participant de cette conversation.");
        }

        $this->messageChatModel->commencerTransaction();
        try {
            $idMessageChat = $this->idGenerator->genererIdentifiantUnique('MSG');

            $data = [
                'id_message_chat' => $idMessageChat,
                'id_conversation' => $idConversation,
                'numero_utilisateur_expediteur' => $numeroExpediteur,
                'contenu_message' => $contenuMessage,
                'date_envoi' => date('Y-m-d H:i:s')
            ];

            if (!$this->messageChatModel->creer($data)) {
                throw new OperationImpossibleException("Échec de l'envoi du message.");
            }

            $participants = $this->participantConversationModel->trouverParCritere(['id_conversation' => $idConversation]);
            foreach ($participants as $participant) {
                if ($participant['numero_utilisateur'] !== $numeroExpediteur) {
                    $this->lectureMessageModel->creer([
                        'id_message_chat' => $idMessageChat,
                        'numero_utilisateur' => $participant['numero_utilisateur'],
                        'date_lecture' => null
                    ]);
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $participant['numero_utilisateur'],
                        'NOUVEAU_MESSAGE',
                        "Vous avez un nouveau message dans la conversation '{$idConversation}'."
                    );
                }
            }

            $this->messageChatModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroExpediteur,
                'ENVOI_MESSAGE',
                "Message envoyé dans conversation {$idConversation}",
                $idMessageChat,
                'MessageChat'
            );
            return $idMessageChat;
        } catch (\Exception $e) {
            $this->messageChatModel->annulerTransaction();
            throw $e;
        }
    }

    public function recupererMessagesDuneConversation(string $idConversation, int $limit = 50, int $offset = 0): array
    {
        if (!$this->conversationModel->trouverParIdentifiant($idConversation)) {
            throw new ElementNonTrouveException("Conversation non trouvée.");
        }
        return $this->messageChatModel->trouverParCritere(
            ['id_conversation' => $idConversation],
            ['*'],
            'AND',
            'date_envoi ASC',
            $limit,
            $offset
        );
    }

    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array
    {
        $participations = $this->participantConversationModel->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur], ['id_conversation']);
        $idsConversations = array_column($participations, 'id_conversation');

        if (empty($idsConversations)) {
            return [];
        }

        return $this->conversationModel->trouverParCritere([
            'id_conversation' => ['operator' => 'in', 'values' => $idsConversations]
        ]);
    }

    public function marquerMessagesCommeLus(string $numeroUtilisateur, string|array $idMessageChat): bool
    {
        $messagesToMark = is_array($idMessageChat) ? $idMessageChat : [$idMessageChat];
        $successCount = 0;

        foreach ($messagesToMark as $msgId) {
            $this->lectureMessageModel->commencerTransaction();
            try {
                $existingEntry = $this->lectureMessageModel->trouverLectureParCles($msgId, $numeroUtilisateur);

                if ($existingEntry && $existingEntry['date_lecture'] === null) {
                    $updateSuccess = $this->lectureMessageModel->mettreAJourLectureParCles(
                        $msgId,
                        $numeroUtilisateur,
                        ['date_lecture' => date('Y-m-d H:i:s')]
                    );
                    if ($updateSuccess) {
                        $successCount++;
                    }
                } elseif (!$existingEntry) {
                    $createSuccess = $this->lectureMessageModel->creer([
                        'id_message_chat' => $msgId,
                        'numero_utilisateur' => $numeroUtilisateur,
                        'date_lecture' => date('Y-m-d H:i:s')
                    ]);
                    if ($createSuccess) {
                        $successCount++;
                    }
                } else {
                    $successCount++;
                }
                $this->lectureMessageModel->validerTransaction();
            } catch (\Exception $e) {
                $this->lectureMessageModel->annulerTransaction();
                error_log("Erreur marquage message {$msgId} comme lu: " . $e->getMessage());
            }
        }
        $this->supervisionService->enregistrerAction(
            $numeroUtilisateur,
            'MARQUER_MESSAGE_LU',
            "Marquage de {$successCount} message(s) comme lus."
        );
        return $successCount === count($messagesToMark);
    }

    public function ajouterParticipant(string $idConversation, array $numerosUtilisateurs): bool
    {
        $conversation = $this->conversationModel->trouverParIdentifiant($idConversation);
        if (!$conversation) {
            throw new ElementNonTrouveException("Conversation non trouvée.");
        }
        if ($conversation['type_conversation'] !== 'Groupe') {
            throw new OperationImpossibleException("Impossible d'ajouter des participants à une conversation qui n'est pas de type 'Groupe'.");
        }

        $this->participantConversationModel->commencerTransaction();
        try {
            $allAdded = true;
            foreach ($numerosUtilisateurs as $numUser) {
                if (!$this->utilisateurModel->trouverParIdentifiant($numUser)) {
                    throw new ElementNonTrouveException("L'utilisateur {$numUser} n'existe pas.");
                }
                if (!$this->participantConversationModel->trouverParticipantParCles($idConversation, $numUser)) {
                    if (!$this->participantConversationModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numUser])) {
                        $allAdded = false;
                        break;
                    }
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $numUser,
                        'AJOUT_CONVERSATION_GROUPE',
                        "Vous avez été ajouté à la conversation de groupe '{$conversation['nom_conversation']}'."
                    );
                }
            }
            if (!$allAdded) {
                throw new OperationImpossibleException("Certains participants n'ont pas pu être ajoutés.");
            }

            $this->participantConversationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'AJOUT_PARTICIPANT_CONVERSATION',
                "Participants ajoutés à la conversation {$idConversation}",
                $idConversation,
                'Conversation'
            );
            return true;
        } catch (\Exception $e) {
            $this->participantConversationModel->annulerTransaction();
            throw $e;
        }
    }

    public function retirerParticipant(string $idConversation, array $numerosUtilisateurs): bool
    {
        $conversation = $this->conversationModel->trouverParIdentifiant($idConversation);
        if (!$conversation) {
            throw new ElementNonTrouveException("Conversation non trouvée.");
        }
        if ($conversation['type_conversation'] !== 'Groupe') {
            throw new OperationImpossibleException("Impossible de retirer des participants d'une conversation qui n'est pas de type 'Groupe'.");
        }

        $this->participantConversationModel->commencerTransaction();
        try {
            $allRemoved = true;
            foreach ($numerosUtilisateurs as $numUser) {
                if (!$this->participantConversationModel->supprimerParticipantParCles($idConversation, $numUser)) {
                    $allRemoved = false;
                    break;
                }
                $this->notificationService->envoyerNotificationUtilisateur(
                    $numUser,
                    'RETRAIT_CONVERSATION_GROUPE',
                    "Vous avez été retiré de la conversation de groupe '{$conversation['nom_conversation']}'."
                );
            }
            if (!$allRemoved) {
                throw new OperationImpossibleException("Certains participants n'ont pas pu être retirés.");
            }

            $this->participantConversationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'RETRAIT_PARTICIPANT_CONVERSATION',
                "Participants retirés de la conversation {$idConversation}",
                $idConversation,
                'Conversation'
            );
            return true;
        } catch (\Exception $e) {
            $this->participantConversationModel->annulerTransaction();
            throw $e;
        }
    }

    public function getConversationDetails(string $idConversation): ?array
    {
        return $this->conversationModel->trouverParIdentifiant($idConversation);
    }

    public function estParticipant(string $idConversation, string $numeroUtilisateur): bool
    {
        return $this->participantConversationModel->trouverParticipantParCles($idConversation, $numeroUtilisateur) !== null;
    }
}