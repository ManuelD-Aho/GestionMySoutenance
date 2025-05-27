<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class ValidationPv extends BaseModel
{
    protected string $table = 'validation_pv';

    public function trouverValidationPvParCles(int $idCompteRendu, string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE id_compte_rendu = :id_compte_rendu AND numero_enseignant = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_INT);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourValidationPvParCles(int $idCompteRendu, string $numeroEnseignant, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE id_compte_rendu = :id_compte_rendu_condition AND numero_enseignant = :numero_enseignant_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['id_compte_rendu_condition'] = $idCompteRendu;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;

        return $declaration->execute($parametres);
    }

    public function supprimerValidationPvParCles(int $idCompteRendu, string $numeroEnseignant): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_compte_rendu = :id_compte_rendu AND numero_enseignant = :numero_enseignant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_INT);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        return $declaration->execute();
    }
}