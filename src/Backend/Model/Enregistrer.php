<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Enregistrer extends BaseModel
{
    protected string $table = 'enregistrer';

    public function trouverEnregistrementParCles(string $numeroUtilisateur, int $idAction, string $dateAction, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE numero_utilisateur = :numero_utilisateur AND id_action = :id_action AND date_action = :date_action";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_action', $idAction, PDO::PARAM_INT);
        $declaration->bindParam(':date_action', $dateAction, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourEnregistrementParCles(string $numeroUtilisateur, int $idAction, string $dateAction, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE numero_utilisateur = :numero_utilisateur_condition AND id_action = :id_action_condition AND date_action = :date_action_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['numero_utilisateur_condition'] = $numeroUtilisateur;
        $parametres['id_action_condition'] = $idAction;
        $parametres['date_action_condition'] = $dateAction;

        return $declaration->execute($parametres);
    }

    public function supprimerEnregistrementParCles(string $numeroUtilisateur, int $idAction, string $dateAction): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE numero_utilisateur = :numero_utilisateur AND id_action = :id_action AND date_action = :date_action";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_action', $idAction, PDO::PARAM_INT);
        $declaration->bindParam(':date_action', $dateAction, PDO::PARAM_STR);
        return $declaration->execute();
    }
}