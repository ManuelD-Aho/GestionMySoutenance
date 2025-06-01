<?php

namespace App\Backend\Model;

use PDO;

class Evaluer extends BaseModel
{
    protected string $table = 'evaluer';

    public function trouverEvaluationParCles(string $numeroCarteEtudiant, string $numeroEnseignant, string $idEcue, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `numero_carte_etudiant` = :numero_carte_etudiant AND `numero_enseignant` = :numero_enseignant AND `id_ecue` = :id_ecue";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_carte_etudiant', $numeroCarteEtudiant, PDO::PARAM_STR);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_ecue', $idEcue, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourEvaluationParCles(string $numeroCarteEtudiant, string $numeroEnseignant, string $idEcue, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `numero_carte_etudiant` = :numero_carte_etudiant_condition AND `numero_enseignant` = :numero_enseignant_condition AND `id_ecue` = :id_ecue_condition";
        $parametres = $donnees;
        $parametres['numero_carte_etudiant_condition'] = $numeroCarteEtudiant;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $parametres['id_ecue_condition'] = $idEcue;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerEvaluationParCles(string $numeroCarteEtudiant, string $numeroEnseignant, string $idEcue): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `numero_carte_etudiant` = :numero_carte_etudiant AND `numero_enseignant` = :numero_enseignant AND `id_ecue` = :id_ecue";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_carte_etudiant', $numeroCarteEtudiant, PDO::PARAM_STR);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_ecue', $idEcue, PDO::PARAM_STR);
        return $declaration->execute();
    }
}