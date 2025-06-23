<?php
namespace App\Backend\Service\Messagerie;

use PDO;
use App\Backend\Model\Conversation;
use App\Backend\Model\MessageChat;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\LectureMessage;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceMessagerie implements ServiceMessagerieInterface
{
    private Conversation $conversationModel;
    private MessageChat $messageChatModel;
    private ParticipantConversation $participantModel;
    private LectureMessage $lectureModel;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(PDO $db, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->conversationModel = new Conversation($db);
        $this->messageChatModel = new MessageChat($db);
        $this->participantModel = new ParticipantConversation($db);
        $this->lectureModel = new LectureMessage($db);
        $this->idGenerator = $idGenerator;
    }

    public function demarrerOuRecupererConversationDirecte(string $numeroUtilisateur1, string $numeroUtilisateur2): string
    {
        // Logique complexe pour trouver une conversation directe existante
        $sql = "SELECT p1.id_conversation FROM participant_conversation p1
                JOIN participant_conversation p2 ON p1.id_conversation = p2.id_conversation
                JOIN conversation c ON p1.id_conversation = c.id_conversation
                WHERE p1.numero_utilisateur = :u1 AND p2.numero_utilisateur = :u2 AND c.type_conversation = 'Direct'
                GROUP BY p1.id_conversation
                HAVING COUNT(p1.id_conversation) = 1";
        $stmt = $this->conversationModel->getDb()->prepare($sql);
        $stmt->execute(['u1' => $numeroUtilisateur1, 'u2' => $numeroUtilisateur2]);
        $result = $stmt->fetch();
        if ($result) return $result['id_conversation'];

        return $this->creerNouvelleConversationDeGroupe("Direct", $numeroUtilisateur1, [$numeroUtilisateur2]);
    }

    public function creerNouvelleConversationDeGroupe(string $nomConversation, string $numeroCreateur, array $numerosParticipants): string
    {
        $this->conversationModel->commencerTransaction();
        try {
            $idConversation = $this->idGenerator->generate('conversation');
            $this->conversationModel->creer(['id_conversation' => $idConversation, 'nom_conversation' => $nomConversation, 'type_conversation' => 'Groupe']);
            $this->participantModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroCreateur]);
            foreach ($numerosParticipants as $participant) {
                $this->participantModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $participant]);
            }
            $this->conversationModel->validerTransaction();
            return $idConversation;
        } catch (\Exception $e) {
            $this->conversationModel->annulerTransaction();
            throw $e;
        }
    }

    public function envoyerMessageDansConversation(string $idConversation, string $numeroExpediteur, string $contenuMessage): string
    {
        $idMessage = $this->idGenerator->generate('message_chat');
        $this->messageChatModel->creer([
            'id_message_chat' => $idMessage,
            'id_conversation' => $idConversation,
            'numero_utilisateur_expediteur' => $numeroExpediteur,
            'contenu_message' => $contenuMessage
        ]);
        return $idMessage;
    }

    public function recupererMessagesDuneConversation(string $idConversation, int $limit = 50, int $offset = 0): array
    {
        return $this->messageChatModel->trouverParCritere(['id_conversation' => $idConversation], ['*'], 'AND', 'date_envoi DESC', $limit, $offset);
    }

    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array
    {
        $participations = $this->participantModel->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur]);
        $ids = array_column($participations, 'id_conversation');
        if (empty($ids)) return [];
        return $this->conversationModel->trouverParCritere(['id_conversation' => ['operator' => 'in', 'values' => $ids]]);
    }

    public function marquerMessagesCommeLus(string $numeroUtilisateur, string|array $idMessageChat): bool
    {
        $ids = is_array($idMessageChat) ? $idMessageChat : [$idMessageChat];
        foreach ($ids as $id) {
            $this->lectureModel->creer(['id_message_chat' => $id, 'numero_utilisateur' => $numeroUtilisateur]);
        }
        return true;
    }

    public function ajouterParticipant(string $idConversation, array $numerosUtilisateurs): bool
    {
        foreach ($numerosUtilisateurs as $user) {
            $this->participantModel->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $user]);
        }
        return true;
    }

    public function retirerParticipant(string $idConversation, array $numerosUtilisateurs): bool
    {
        foreach ($numerosUtilisateurs as $user) {
            $this->participantModel->supprimerParClesInternes(['id_conversation' => $idConversation, 'numero_utilisateur' => $user]);
        }
        return true;
    }
}