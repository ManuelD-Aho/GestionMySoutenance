<?php

namespace Backend\Model;

use PDO;

class Rendre extends BaseModel
{
    protected string $table = 'rendre';

    public function trouverParCleComposite(string $numeroEnseignant, int $idCompteRendu, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE numero_enseignant = :numero_enseignant AND id_compte_rendu = :id_compte_rendu";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_INT);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourParCleComposite(string $numeroEnseignant, int $idCompteRendu, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE numero_enseignant = :numero_enseignant_condition AND id_compte_rendu = :id_compte_rendu_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $parametres['id_compte_rendu_condition'] = $idCompteRendu;

        return $declaration->execute($parametres);
    }

    public function supprimerParCleComposite(string $numeroEnseignant, int $idCompteRendu): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE numero_enseignant = :numero_enseignant AND id_compte_rendu = :id_compte_rendu";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_INT);
        return $declaration->execute();
    }
}