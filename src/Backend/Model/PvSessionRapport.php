<?php

namespace App\Backend\Model;

use PDO;

class PvSessionRapport extends BaseModel
{
    protected string $table = 'pv_session_rapport';

    public function trouverLiaisonPvSessionRapportParCles(string $idCompteRendu, string $idRapportEtudiant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `id_compte_rendu` = :id_compte_rendu AND `id_rapport_etudiant` = :id_rapport_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_STR);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_STR);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function supprimerLiaisonPvSessionRapportParCles(string $idCompteRendu, string $idRapportEtudiant): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id_compte_rendu` = :id_compte_rendu AND `id_rapport_etudiant` = :id_rapport_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_STR);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_STR);
        return $declaration->execute();
    }
}