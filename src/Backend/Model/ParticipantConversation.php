<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class ParticipantConversation extends BaseModel
{
    protected string $table = 'participant_conversation';

    public function trouverParticipantParCles(int $idConversation, string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE id_conversation = :id_conversation AND numero_utilisateur = :numero_utilisateur";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_conversation', $idConversation, PDO::PARAM_INT);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function supprimerParticipantParCles(int $idConversation, string $numeroUtilisateur): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_conversation = :id_conversation AND numero_utilisateur = :numero_utilisateur";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_conversation', $idConversation, PDO::PARAM_INT);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        return $declaration->execute();
    }
}