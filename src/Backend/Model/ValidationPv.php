<?php

namespace App\Backend\Model;

use PDO;

class ValidationPv extends BaseModel
{
    protected string $table = 'validation_pv';

    public function trouverValidationPvParCles(string $idCompteRendu, string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `id_compte_rendu` = :id_compte_rendu AND `numero_enseignant` = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_STR);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourValidationPvParCles(string $idCompteRendu, string $numeroEnseignant, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `id_compte_rendu` = :id_compte_rendu_condition AND `numero_enseignant` = :numero_enseignant_condition";
        $parametres = $donnees;
        $parametres['id_compte_rendu_condition'] = $idCompteRendu;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerValidationPvParCles(string $idCompteRendu, string $numeroEnseignant): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id_compte_rendu` = :id_compte_rendu AND `numero_enseignant` = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_STR);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        return $declaration->execute();
    }
}