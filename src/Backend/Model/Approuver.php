<?php
namespace App\Backend\Model;
use PDO;

class Approuver extends BaseModel
{
    protected string $table = 'approuver';

    public function trouverParCleComposite(string $numeroPersonnelAdministratif, int $idRapportEtudiant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE numero_personnel_administratif = :numero_personnel_administratif AND id_rapport_etudiant = :id_rapport_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_personnel_administratif', $numeroPersonnelAdministratif, PDO::PARAM_STR);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_INT);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourParCleComposite(string $numeroPersonnelAdministratif, int $idRapportEtudiant, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE numero_personnel_administratif = :numero_personnel_administratif_condition AND id_rapport_etudiant = :id_rapport_etudiant_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['numero_personnel_administratif_condition'] = $numeroPersonnelAdministratif;
        $parametres['id_rapport_etudiant_condition'] = $idRapportEtudiant;

        return $declaration->execute($parametres);
    }

    public function supprimerParCleComposite(string $numeroPersonnelAdministratif, int $idRapportEtudiant): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE numero_personnel_administratif = :numero_personnel_administratif AND id_rapport_etudiant = :id_rapport_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_personnel_administratif', $numeroPersonnelAdministratif, PDO::PARAM_STR);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_INT);
        return $declaration->execute();
    }
}