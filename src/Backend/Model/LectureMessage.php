<?php

namespace App\Backend\Model;

use PDO;

class LectureMessage extends BaseModel
{
    protected string $table = 'lecture_message';

    public function trouverLectureParCles(string $idMessageChat, string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `id_message_chat` = :id_message_chat AND `numero_utilisateur` = :numero_utilisateur";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_message_chat', $idMessageChat, PDO::PARAM_STR);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourLectureParCles(string $idMessageChat, string $numeroUtilisateur, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `id_message_chat` = :id_message_chat_condition AND `numero_utilisateur` = :numero_utilisateur_condition";
        $parametres = $donnees;
        $parametres['id_message_chat_condition'] = $idMessageChat;
        $parametres['numero_utilisateur_condition'] = $numeroUtilisateur;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerLectureParCles(string $idMessageChat, string $numeroUtilisateur): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id_message_chat` = :id_message_chat AND `numero_utilisateur` = :numero_utilisateur";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_message_chat', $idMessageChat, PDO::PARAM_STR);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        return $declaration->execute();
    }
}