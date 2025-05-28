<?php

namespace App\Backend\Service\Messagerie;

use App\Backend\Model\Conversation;
use App\Backend\Model\LectureMessage;
use App\Backend\Model\MessageChat;
use App\Backend\Model\ParticipantConversation;
use PDO;

class ServiceMessagerie
{
    private Conversation $modeleConversation;
    private ParticipantConversation $modeleParticipantConversation;
    private MessageChat $modeleMessageChat;
    private LectureMessage $modeleLectureMessage;
    private PDO $db;

    public function __construct(
        Conversation $modeleConversation,
        ParticipantConversation $modeleParticipantConversation,
        MessageChat $modeleMessageChat,
        LectureMessage $modeleLectureMessage,
        PDO $db
    ) {
        $this->modeleConversation = $modeleConversation;
        $this->modeleParticipantConversation = $modeleParticipantConversation;
        $this->modeleMessageChat = $modeleMessageChat;
        $this->modeleLectureMessage = $modeleLectureMessage;
        $this->db = $db;
    }

    public function demarrerOuRecupererConversationDirecte(string $numeroUtilisateurA, string $numeroUtilisateurB): ?int
    {
        $sql = "SELECT pcA.id_conversation
                FROM participant_conversation pcA
                JOIN participant_conversation pcB ON pcA.id_conversation = pcB.id_conversation
                JOIN conversation c ON pcA.id_conversation = c.id_conversation
                WHERE pcA.numero_utilisateur = :userA 
                  AND pcB.numero_utilisateur = :userB 
                  AND c.type_conversation = 'Direct'
                  AND (SELECT COUNT(*) FROM participant_conversation pc_count WHERE pc_count.id_conversation = c.id_conversation) = 2
                LIMIT 1";
        $declaration = $this->db->prepare($sql);
        $declaration->execute([':userA' => $numeroUtilisateurA, ':userB' => $numeroUtilisateurB]);
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);

        if ($resultat && isset($resultat['id_conversation'])) {
            return (int)$resultat['id_conversation'];
        }

        $this->db->beginTransaction();
        try {
            $idConversation = $this->modeleConversation->creer(['type_conversation' => 'Direct', 'date_creation_conv' => date('Y-m-d H:i:s')]);
            if (!$idConversation) {
                $this->db->rollBack(); return null;
            }
            $idConversation = (int)$idConversation;

            $this->modeleParticipantConversation->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroUtilisateurA]);
            $this->modeleParticipantConversation->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroUtilisateurB]);

            $this->db->commit();
            return $idConversation;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function creerNouvelleConversationDeGroupe(string $nomConversation, array $numerosParticipantsInitiateurs, string $numeroCreateur): ?int
    {
        $this->db->beginTransaction();
        try {
            $idConversation = $this->modeleConversation->creer([
                'nom_conversation' => $nomConversation,
                'type_conversation' => 'Groupe',
                'date_creation_conv' => date('Y-m-d H:i:s')
            ]);
            if (!$idConversation) {
                $this->db->rollBack(); return null;
            }
            $idConversation = (int)$idConversation;

            if (!in_array($numeroCreateur, $numerosParticipantsInitiateurs)) {
                $numerosParticipantsInitiateurs[] = $numeroCreateur;
            }
            $numerosParticipantsUniques = array_unique($numerosParticipantsInitiateurs);

            foreach ($numerosParticipantsUniques as $numParticipant) {
                if(!$this->modeleParticipantConversation->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numParticipant])) {
                    $this->db->rollBack(); return null;
                }
            }
            $this->db->commit();
            return $idConversation;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function envoyerMessageDansConversation(int $idConversation, string $numeroUtilisateurExpediteur, string $contenuMessage): ?int
    {
        $idMessage = $this->modeleMessageChat->creer([
            'id_conversation' => $idConversation,
            'numero_utilisateur_expediteur' => $numeroUtilisateurExpediteur,
            'contenu_message' => $contenuMessage,
            'date_envoi' => date('Y-m-d H:i:s')
        ]);
        return $idMessage ? (int)$idMessage : null;
    }

    public function recupererMessagesDuneConversation(int $idConversation, int $limite = 20, int $offset = 0): array
    {
        $sql = "SELECT mc.*, u.login_utilisateur as expediteur_login 
                FROM message_chat mc 
                JOIN utilisateur u ON mc.numero_utilisateur_expediteur = u.numero_utilisateur
                WHERE mc.id_conversation = :id_conversation 
                ORDER BY mc.date_envoi DESC 
                LIMIT :limite OFFSET :offset";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_conversation', $idConversation, PDO::PARAM_INT);
        $declaration->bindParam(':limite', $limite, PDO::PARAM_INT);
        $declaration->bindParam(':offset', $offset, PDO::PARAM_INT);
        $declaration->execute();
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array
    {
        $sql = "SELECT c.*, GROUP_CONCAT(DISTINCT u.login_utilisateur SEPARATOR ', ') as participants_logins,
                       (SELECT mc.contenu_message FROM message_chat mc WHERE mc.id_conversation = c.id_conversation ORDER BY mc.date_envoi DESC LIMIT 1) as dernier_message,
                       (SELECT mc.date_envoi FROM message_chat mc WHERE mc.id_conversation = c.id_conversation ORDER BY mc.date_envoi DESC LIMIT 1) as date_dernier_message
                FROM conversation c
                JOIN participant_conversation pc_self ON c.id_conversation = pc_self.id_conversation AND pc_self.numero_utilisateur = :numero_utilisateur
                JOIN participant_conversation pc_others ON c.id_conversation = pc_others.id_conversation
                JOIN utilisateur u ON pc_others.numero_utilisateur = u.numero_utilisateur
                GROUP BY c.id_conversation
                ORDER BY date_dernier_message DESC";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->execute();
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function marquerMessagesCommeLus(int $idConversation, string $numeroUtilisateurLecteur): bool
    {
        $sql = "INSERT INTO lecture_message (id_message_chat, numero_utilisateur, date_lecture)
                SELECT mc.id_message_chat, :numero_utilisateur, NOW()
                FROM message_chat mc
                WHERE mc.id_conversation = :id_conversation
                  AND mc.numero_utilisateur_expediteur != :numero_utilisateur
                  AND NOT EXISTS (
                      SELECT 1 FROM lecture_message lm 
                      WHERE lm.id_message_chat = mc.id_message_chat 
                      AND lm.numero_utilisateur = :numero_utilisateur
                  )
                ON DUPLICATE KEY UPDATE date_lecture = NOW()";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateurLecteur, PDO::PARAM_STR);
        $declaration->bindParam(':id_conversation', $idConversation, PDO::PARAM_INT);
        return $declaration->execute();
    }

    public function ajouterParticipant(int $idConversation, string $numeroUtilisateurAAjouter, string $numeroUtilisateurQuiAjoute): bool
    {
        $participantExistant = $this->modeleParticipantConversation->trouverParticipantParCles($idConversation, $numeroUtilisateurAAjouter);
        if ($participantExistant) return true;
        return (bool)$this->modeleParticipantConversation->creer(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroUtilisateurAAjouter]);
    }

    public function retirerParticipant(int $idConversation, string $numeroUtilisateurARetirer, string $numeroUtilisateurQuiRetire): bool
    {
        return $this->modeleParticipantConversation->supprimerParticipantParCles($idConversation, $numeroUtilisateurARetirer);
    }
}