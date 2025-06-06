<?php

namespace App\Backend\Model;

use PDO;

class Attribuer extends BaseModel
{
    protected string $table = 'attribuer';

    public function trouverAttributionParCles(string $numeroEnseignant, string $idSpecialite, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_specialite` = :id_specialite";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_specialite', $idSpecialite, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourAttributionParCles(string $numeroEnseignant, string $idSpecialite, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `numero_enseignant` = :numero_enseignant_condition AND `id_specialite` = :id_specialite_condition";
        $parametres = $donnees;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $parametres['id_specialite_condition'] = $idSpecialite;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerAttributionParCles(string $numeroEnseignant, string $idSpecialite): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_specialite` = :id_specialite";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_specialite', $idSpecialite, PDO::PARAM_STR);
        return $declaration->execute();
    }
}