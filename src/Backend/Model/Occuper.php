<?php

namespace App\Backend\Model;

use PDO;

class Occuper extends BaseModel
{
    protected string $table = 'occuper';

    public function trouverOccupationParCles(string $idFonction, string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `id_fonction` = :id_fonction AND `numero_enseignant` = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_fonction', $idFonction, PDO::PARAM_STR);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourOccupationParCles(string $idFonction, string $numeroEnseignant, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `id_fonction` = :id_fonction_condition AND `numero_enseignant` = :numero_enseignant_condition";
        $parametres = $donnees;
        $parametres['id_fonction_condition'] = $idFonction;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerOccupationParCles(string $idFonction, string $numeroEnseignant): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id_fonction` = :id_fonction AND `numero_enseignant` = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_fonction', $idFonction, PDO::PARAM_STR);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        return $declaration->execute();
    }
}