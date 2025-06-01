<?php

namespace App\Backend\Model;

use PDO;

class Affecter extends BaseModel
{
    protected string $table = 'affecter';

    public function trouverAffectationParCles(string $numeroEnseignant, string $idRapportEtudiant, string $idStatutJury, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_rapport_etudiant` = :id_rapport_etudiant AND `id_statut_jury` = :id_statut_jury";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_STR);
        $declaration->bindParam(':id_statut_jury', $idStatutJury, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourAffectationParCles(string $numeroEnseignant, string $idRapportEtudiant, string $idStatutJury, array $donnees): bool
    {
        if (empty($donnees)) return false;
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) $setClause[] = "`{$colonne}` = :{$colonne}";
        $setString = implode(', ', $setClause);
        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `numero_enseignant` = :numero_enseignant_condition AND `id_rapport_etudiant` = :id_rapport_etudiant_condition AND `id_statut_jury` = :id_statut_jury_condition";
        $parametres = $donnees;
        $parametres['numero_enseignant_condition'] = $numeroEnseignant;
        $parametres['id_rapport_etudiant_condition'] = $idRapportEtudiant;
        $parametres['id_statut_jury_condition'] = $idStatutJury;
        $declaration = $this->db->prepare($sql);
        return $declaration->execute($parametres);
    }

    public function supprimerAffectationParCles(string $numeroEnseignant, string $idRapportEtudiant, string $idStatutJury): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `numero_enseignant` = :numero_enseignant AND `id_rapport_etudiant` = :id_rapport_etudiant AND `id_statut_jury` = :id_statut_jury";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_enseignant', $numeroEnseignant, PDO::PARAM_STR);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_STR);
        $declaration->bindParam(':id_statut_jury', $idStatutJury, PDO::PARAM_STR);
        return $declaration->execute();
    }
}