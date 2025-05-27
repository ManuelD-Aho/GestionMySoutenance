<?php

namespace Backend\Model;

use PDO;

class FaireStage extends BaseModel
{
    protected string $table = 'faire_stage';

    public function trouverParCleComposite(int $idEntreprise, string $numeroCarteEtudiant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE id_entreprise = :id_entreprise AND numero_carte_etudiant = :numero_carte_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_entreprise', $idEntreprise, PDO::PARAM_INT);
        $declaration->bindParam(':numero_carte_etudiant', $numeroCarteEtudiant, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourParCleComposite(int $idEntreprise, string $numeroCarteEtudiant, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE id_entreprise = :id_entreprise_condition AND numero_carte_etudiant = :numero_carte_etudiant_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['id_entreprise_condition'] = $idEntreprise;
        $parametres['numero_carte_etudiant_condition'] = $numeroCarteEtudiant;

        return $declaration->execute($parametres);
    }

    public function supprimerParCleComposite(int $idEntreprise, string $numeroCarteEtudiant): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_entreprise = :id_entreprise AND numero_carte_etudiant = :numero_carte_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_entreprise', $idEntreprise, PDO::PARAM_INT);
        $declaration->bindParam(':numero_carte_etudiant', $numeroCarteEtudiant, PDO::PARAM_STR);
        return $declaration->execute();
    }
}