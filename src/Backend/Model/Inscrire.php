<?php

namespace Backend\Model;

use PDO;

class Inscrire extends BaseModel
{
    protected string $table = 'inscrire';

    public function trouverParCleComposite(string $numeroCarteEtudiant, int $idNiveauEtude, int $idAnneeAcademique, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE numero_carte_etudiant = :numero_carte_etudiant AND id_niveau_etude = :id_niveau_etude AND id_annee_academique = :id_annee_academique";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_carte_etudiant', $numeroCarteEtudiant, PDO::PARAM_STR);
        $declaration->bindParam(':id_niveau_etude', $idNiveauEtude, PDO::PARAM_INT);
        $declaration->bindParam(':id_annee_academique', $idAnneeAcademique, PDO::PARAM_INT);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function mettreAJourParCleComposite(string $numeroCarteEtudiant, int $idNiveauEtude, int $idAnneeAcademique, array $donnees): bool
    {
        if (empty($donnees)) {
            return false;
        }
        $setClause = [];
        foreach (array_keys($donnees) as $colonne) {
            $setClause[] = "{$colonne} = :{$colonne}";
        }
        $setString = implode(', ', $setClause);
        $sql = "UPDATE {$this->table} SET {$setString} WHERE numero_carte_etudiant = :numero_carte_etudiant_condition AND id_niveau_etude = :id_niveau_etude_condition AND id_annee_academique = :id_annee_academique_condition";
        $declaration = $this->db->prepare($sql);

        $parametres = $donnees;
        $parametres['numero_carte_etudiant_condition'] = $numeroCarteEtudiant;
        $parametres['id_niveau_etude_condition'] = $idNiveauEtude;
        $parametres['id_annee_academique_condition'] = $idAnneeAcademique;

        return $declaration->execute($parametres);
    }

    public function supprimerParCleComposite(string $numeroCarteEtudiant, int $idNiveauEtude, int $idAnneeAcademique): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE numero_carte_etudiant = :numero_carte_etudiant AND id_niveau_etude = :id_niveau_etude AND id_annee_academique = :id_annee_academique";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':numero_carte_etudiant', $numeroCarteEtudiant, PDO::PARAM_STR);
        $declaration->bindParam(':id_niveau_etude', $idNiveauEtude, PDO::PARAM_INT);
        $declaration->bindParam(':id_annee_academique', $idAnneeAcademique, PDO::PARAM_INT);
        return $declaration->execute();
    }
}