<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Pister extends BaseModel
{
    protected string $table = 'pister';

    public function trouverPisteParCles(string $numeroUtilisateur, int $idTraitement, string $datePister, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE numero_utilisateur = :numero_utilisateur AND id_traitement = :id_traitement AND date_pister = :date_pister";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_traitement', $idTraitement, PDO::PARAM_INT);
        $declaration->bindParam(':date_pister', $datePister, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourPisteParCles(string $numeroUtilisateur, int $idTraitement, string $datePister, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE numero_utilisateur = :numero_utilisateur_condition AND id_traitement = :id_traitement_condition AND date_pister = :date_pister_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['numero_utilisateur_condition'] = $numeroUtilisateur;
        $parametres['id_traitement_condition'] = $idTraitement;
        $parametres['date_pister_condition'] = $datePister;

        return $declaration->execute($parametres);
    }

    public function supprimerPisteParCles(string $numeroUtilisateur, int $idTraitement, string $datePister): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE numero_utilisateur = :numero_utilisateur AND id_traitement = :id_traitement AND date_pister = :date_pister";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_utilisateur', $numeroUtilisateur, PDO::PARAM_STR);
        $declaration->bindParam(':id_traitement', $idTraitement, PDO::PARAM_INT);
        $declaration->bindParam(':date_pister', $datePister, PDO::PARAM_STR);
        return $declaration->execute();
    }
}