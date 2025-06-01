<?php

namespace App\Backend\Model;

use PDO;

class Rendre extends BaseModel
{
    protected string $table = 'rendre';

    public function trouverActionRenduParCles(string $numeroEnseignant, string $idCompteRendu, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_compte_rendu` = :id_compte_rendu";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourActionRenduParCles(string $numeroEnseignant, string $idCompteRendu, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `numero_enseignant` = :numero_enseignant_condition AND `id_compte_rendu` = :id_compte_rendu_condition";
        $parametres = $donnees;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $parametres['id_compte_rendu_condition'] = $idCompteRendu;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerActionRenduParCles(string $numeroEnseignant, string $idCompteRendu): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_compte_rendu` = :id_compte_rendu";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_STR);
        return $declaration->execute();
    }
}