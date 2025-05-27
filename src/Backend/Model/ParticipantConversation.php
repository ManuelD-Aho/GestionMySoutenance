<?php

namespace Backend\Model;

use PDO;

class ParticipantConversation extends BaseModel
{
    protected string $table = 'participant_conversation';

    public function trouverParCleComposite(int $idConversation, string $numeroUtilisateur, array $colonnes = ['*']): ?array
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

    public function supprimerParCleComposite(int $idConversation, string $numeroUtilisateur): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_conversation = :id_conversation AND numero_utilisateur = :numero_utilisateur";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_conversation', $idConversation, PDO::PARAM_INT);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        return $declaration->execute();
    }
}