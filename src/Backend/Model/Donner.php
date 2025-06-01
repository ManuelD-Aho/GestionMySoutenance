<?php

namespace App\Backend\Model;

use PDO;

class Donner extends BaseModel
{
    protected string $table = 'donner';

    public function trouverDonParCles(string $numeroEnseignant, string $idNiveauApprobation, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_niveau_approbation` = :id_niveau_approbation";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_niveau_approbation', $idNiveauApprobation, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourDonParCles(string $numeroEnseignant, string $idNiveauApprobation, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `numero_enseignant` = :numero_enseignant_condition AND `id_niveau_approbation` = :id_niveau_approbation_condition";
        $parametres = $donnees;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $parametres['id_niveau_approbation_condition'] = $idNiveauApprobation;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerDonParCles(string $numeroEnseignant, string $idNiveauApprobation): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_niveau_approbation` = :id_niveau_approbation";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_niveau_approbation', $idNiveauApprobation, PDO::PARAM_STR);
        return $declaration->execute();
    }
}