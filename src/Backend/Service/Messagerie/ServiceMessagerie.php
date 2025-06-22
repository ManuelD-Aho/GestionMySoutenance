<?php
namespace App\Backend\Service\Messagerie;

use PDO;
use App\Backend\Model\Conversation;
use App\Backend\Model\MessageChat;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\LectureMessage;
use App\Backend\Model\Utilisateur; // Pour vérifier l'existence des utilisateurs
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator; // Pour générer les IDs
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceMessagerie implements ServiceMessagerieInterface
{
    private Conversation $conversationModel;
    private MessageChat $messageChatModel;
    private ParticipantConversation $participantConversationModel;
    private LectureMessage $lectureMessageModel;
    private Utilisateur $utilisateurModel; // Pour vérifier les participants
    private ServiceNotification $notificationService;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator;

    public function __construct(
        PDO $db,
        ServiceNotification $notificationService,
        ServiceSupervisionAdmin $supervisionService,
        IdentifiantGenerator $idGenerator
    ) {
        $this->conversationModel = new Conversation($db);
        $this->messageChatModel = new MessageChat($db);
        $this->participantConversationModel = new ParticipantConversation($db);
        $this->lectureMessageModel = new LectureMessage($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    /**
     * Démarre une conversation directe entre deux utilisateurs, ou récupère l'existante.
     * @param string $numeroUtilisateur1 Le numéro du premier utilisateur.
     * @param string $numeroUtilisateur2 Le numéro du second utilisateur.
     * @return string L'ID de la conversation.
     * @throws ElementNonTrouveException Si l'un des utilisateurs n'existe pas.
     * @throws OperationImpossibleException En cas d'erreur de création.
     */
    public function demarrerOuRecupererConversationDirecte(string $numeroUtilisateur1, string $numeroUtilisateur2): string
    {
        if (!$this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur1) || !$this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur2)) {
            throw new ElementNonTrouveException("Un ou plusieurs utilisateurs n'existent pas.");
        }

        // Chercher si une conversation directe entre ces deux existe déjà
        $conversationsUser1 = $this->participantConversationModel->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur1], ['id_conversation']);
        $conversationsUser2 = $this->participantConversationModel->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur2], ['id_conversation']);

        $commonConversations = array_intersect(array_column($conversationsUser1, 'id_conversation'), array_column($conversationsUser2, 'id_conversation'));

        foreach ($commonConversations as $convId) {
            $conv = $this->conversationModel->trouverParIdentifiant($convId);
            if ($conv && $conv['type_conversation'] === 'Direct') {
                // Vérifier qu'il n'y a que 2 participants
                $participants = $this->participantConversationModel->compterParCritere(['id_conversation' => $convId]);
                if ($participants === 2) {
                    return $convId; // Conversation directe existante trouvée
                }
            }
        }

        // Si non trouvée, créer une nouvelle conversation directe
        $this->conversationModel->commencerTransaction();
        try {
            $idConversation = $this->idGenerator->genererIdentifiantUnique('CONV'); // CONV-AAAA-SSSS
            $conversationName = "Conversation entre " . $numeroUtilisateur1 . " et " . $numeroUtilisateur2; // Nom auto généré

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
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CREATION_CONVERSATION_DIRECTE',
                "Erreur création conversation directe: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Crée une nouvelle conversation de groupe.
     * @param string $nomConversation Le nom du groupe.
     * @param string $numeroCreateur Le numéro de l'utilisateur créateur du groupe.
     * @param array $numerosParticipants Tableau des numéros d'utilisateurs participants.
     * @return string L'ID de la conversation de groupe créée.
     * @throws ElementNonTrouveException Si un participant n'existe pas.
     * @throws OperationImpossibleException En cas d'erreur de création.
     */
    public function creerNouvelleConversationDeGroupe(string $nomConversation, string $numeroCreateur, array $numerosParticipants): string
    {
        $this->conversationModel->commencerTransaction();
        try {
            $idConversation = $this->idGenerator->genererIdentifiantUnique('CONV'); // CONV-AAAA-SSSS

            $data = [
                'id_conversation' => $idConversation,
                'nom_conversation' => $nomConversation,
                'type_conversation' => 'Groupe'
            ];

            if (!$this->conversationModel->creer($data)) {
                throw new OperationImpossibleException("Échec de la création de la conversation de groupe.");
            }

            // Ajouter le créateur comme participant
            if (!$this->participantConversationModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroCreateur])) {
                throw new OperationImpossibleException("Échec d'ajout du créateur à la conversation.");
            }

            // Ajouter les autres participants
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
            $this->supervisionService->enregistrerAction(
                $numeroCreateur,
                'ECHEC_CREATION_CONVERSATION_GROUPE',
                "Erreur création conversation de groupe: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Envoie un message dans une conversation donnée.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroExpediteur Le numéro de l'utilisateur expéditeur.
     * @param string $contenuMessage Le contenu du message.
     * @return string L'ID du message de chat créé.
     * @throws ElementNonTrouveException Si la conversation ou l'expéditeur n'existe pas.
     * @throws OperationImpossibleException Si l'expéditeur n'est pas participant ou en cas d'erreur.
     */
    public function envoyerMessageDansConversation(string $idConversation, string $numeroExpediteur, string $contenuMessage): string
    {
        if (!$this->conversationModel->trouverParIdentifiant($idConversation)) {
            throw new ElementNonTrouveException("Conversation non trouvée.");
        }
        if (!$this->utilisateurModel->trouverParIdentifiant($numeroExpediteur)) {
            throw new ElementNonTrouveException("Expéditeur non trouvé.");
        }
        // Vérifier que l'expéditeur est bien un participant de cette conversation
        if (!$this->participantConversationModel->trouverParticipantParCles($idConversation, $numeroExpediteur)) {
            throw new OperationImpossibleException("L'expéditeur n'est pas un participant de cette conversation.");
        }

        $this->messageChatModel->commencerTransaction();
        try {
            $idMessageChat = $this->idGenerator->genererIdentifiantUnique('MSG'); // MSG-AAAA-SSSS

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

            // Pour chaque participant de la conversation (sauf l'expéditeur), marquer le message comme non lu et notifier
            $participants = $this->participantConversationModel->trouverParCritere(['id_conversation' => $idConversation]);
            foreach ($participants as $participant) {
                if ($participant['numero_utilisateur'] !== $numeroExpediteur) {
                    // Créer une entrée dans lecture_message avec date_lecture = null (non lu)
                    // ou marquer comme non lu implicitement et juste notifier
                    // Ici on crée une entrée par défaut et la date_lecture sera mise à jour lors de la lecture
                    $this->lectureMessageModel->creer([
                        'id_message_chat' => $idMessageChat,
                        'numero_utilisateur' => $participant['numero_utilisateur'],
                        'date_lecture' => null // Non lu
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
            $this->supervisionService->enregistrerAction(
                $numeroExpediteur,
                'ECHEC_ENVOI_MESSAGE',
                "Erreur envoi message conversation {$idConversation}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Récupère les messages d'une conversation donnée.
     * @param string $idConversation L'ID de la conversation.
     * @param int $limit Le nombre maximum de messages à récupérer.
     * @param int $offset L'offset pour la pagination.
     * @return array Liste des messages de chat.
     * @throws ElementNonTrouveException Si la conversation n'existe pas.
     */
    public function recupererMessagesDuneConversation(string $idConversation, int $limit = 50, int $offset = 0): array
    {
        if (!$this->conversationModel->trouverParIdentifiant($idConversation)) {
            throw new ElementNonTrouveException("Conversation non trouvée.");
        }
        // Récupérer les messages, éventuellement avec jointure sur utilisateur pour les noms des expéditeurs
        return $this->messageChatModel->trouverParCritere(
            ['id_conversation' => $idConversation],
            ['*'],
            'AND',
            'date_envoi ASC', // Ordre chronologique
            $limit,
            $offset
        );
    }

    /**
     * Liste toutes les conversations auxquelles un utilisateur participe.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return array Liste des conversations.
     */
    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array
    {
        // Récupérer les ID des conversations où l'utilisateur est participant
        $participations = $this->participantConversationModel->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur], ['id_conversation']);
        $idsConversations = array_column($participations, 'id_conversation');

        if (empty($idsConversations)) {
            return [];
        }

        // Récupérer les détails complets des conversations
        return $this->conversationModel->trouverParCritere([
            'id_conversation' => ['operator' => 'in', 'values' => $idsConversations]
        ]);
    }

    /**
     * Marque un ou plusieurs messages comme lus pour un utilisateur donné.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string|array $idMessageChat L'ID du message ou un tableau d'IDs de messages à marquer comme lus.
     * @return bool Vrai si la mise à jour a réussi.
     */
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
                    // Si l'entrée n'existe pas, la créer comme lue (par exemple, si l'expéditeur marque comme lu directement)
                    $createSuccess = $this->lectureMessageModel->creer([
                        'id_message_chat' => $msgId,
                        'numero_utilisateur' => $numeroUtilisateur,
                        'date_lecture' => date('Y-m-d H:i:s')
                    ]);
                    if ($createSuccess) {
                        $successCount++;
                    }
                } else {
                    $successCount++; // Déjà lu
                }
                $this->lectureMessageModel->validerTransaction();
            } catch (\Exception $e) {
                $this->lectureMessageModel->annulerTransaction();
                // Log error but continue with other messages
                $this->supervisionService->enregistrerAction(
                    $numeroUtilisateur,
                    'ECHEC_MARQUER_MESSAGE_LU',
                    "Erreur marquage message {$msgId} comme lu: " . $e->getMessage()
                );
            }
        }
        $this->supervisionService->enregistrerAction(
            $numeroUtilisateur,
            'MARQUER_MESSAGE_LU',
            "Marquage de {$successCount} message(s) comme lus."
        );
        return $successCount === count($messagesToMark);
    }

    /**
     * Ajoute un ou plusieurs participants à une conversation de groupe.
     * @param string $idConversation L'ID de la conversation de groupe.
     * @param array $numerosUtilisateurs Tableau des numéros d'utilisateurs à ajouter.
     * @return bool Vrai si tous les participants ont été ajoutés.
     * @throws ElementNonTrouveException Si la conversation ou un utilisateur n'est pas trouvé.
     * @throws OperationImpossibleException Si la conversation n'est pas de type 'Groupe'.
     */
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
                // Vérifier si déjà participant
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
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_AJOUT_PARTICIPANT_CONVERSATION',
                "Erreur ajout participant conversation {$idConversation}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Retire un ou plusieurs participants d'une conversation de groupe.
     * @param string $idConversation L'ID de la conversation de groupe.
     * @param array $numerosUtilisateurs Tableau des numéros d'utilisateurs à retirer.
     * @return bool Vrai si tous les participants ont été retirés.
     * @throws ElementNonTrouveException Si la conversation n'est pas trouvée.
     * @throws OperationImpossibleException Si la conversation n'est pas de type 'Groupe'.
     */
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
                // Vérifier si au moins 2 participants restent si on retire un participant
                $currentParticipantsCount = $this->participantConversationModel->compterParCritere(['id_conversation' => $idConversation]);
                if ($currentParticipantsCount <= 1 && $numUser !== $conversation['numero_createur_initial'] ?? null) { // Gérer le cas du dernier participant
                    // Optionnel: Gérer la suppression de la conversation si c'est le dernier participant à la quitter
                    // throw new OperationImpossibleException("Impossible de retirer le dernier participant d'une conversation.");
                }

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
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_RETRAIT_PARTICIPANT_CONVERSATION',
                "Erreur retrait participant conversation {$idConversation}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Récupère les détails d'une conversation spécifique par son ID.
     * @param string $idConversation L'ID de la conversation.
     * @return array|null Les détails de la conversation ou null si non trouvée.
     */
    public function getConversationDetails(string $idConversation): ?array
    {
        return $this->conversationModel->trouverParIdentifiant($idConversation);
    }

    /**
     * Vérifie si un utilisateur est participant d'une conversation donnée.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si l'utilisateur est participant, faux sinon.
     */
    public function estParticipant(string $idConversation, string $numeroUtilisateur): bool
    {
        return $this->participantConversationModel->trouverParticipantParCles($idConversation, $numeroUtilisateur) !== null;
    }
}