<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Acquerir extends BaseModel
{
    protected string $table = 'acquerir';

    public function trouverAcquisitionParCles(int $idGrade, string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE id_grade = :id_grade AND numero_enseignant = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_grade', $idGrade, PDO::PARAM_INT);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourAcquisitionParCles(int $idGrade, string $numeroEnseignant, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE id_grade = :id_grade_condition AND numero_enseignant = :numero_enseignant_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['id_grade_condition'] = $idGrade;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;

        return $declaration->execute($parametres);
    }

    public function supprimerAcquisitionParCles(int $idGrade, string $numeroEnseignant): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_grade = :id_grade AND numero_enseignant = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_grade', $idGrade, PDO::PARAM_INT);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        return $declaration->execute();
    }
}