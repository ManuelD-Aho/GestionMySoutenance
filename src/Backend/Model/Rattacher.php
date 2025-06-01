<?php

namespace App\Backend\Model;

use PDO;

class Rattacher extends BaseModel
{
    protected string $table = 'rattacher';

    public function trouverRattachementParCles(string $idGroupeUtilisateur, string $idTraitement, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `id_groupe_utilisateur` = :id_groupe_utilisateur AND `id_traitement` = :id_traitement";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_groupe_utilisateur', $idGroupeUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_traitement', $idTraitement, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function supprimerRattachementParCles(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id_groupe_utilisateur` = :id_groupe_utilisateur AND `id_traitement` = :id_traitement";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_groupe_utilisateur', $idGroupeUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_traitement', $idTraitement, PDO::PARAM_STR);
        return $declaration->execute();
    }
}