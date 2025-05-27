<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class PvSessionRapport extends BaseModel
{
    protected string $table = 'pv_session_rapport';

    public function trouverLiaisonPvSessionRapportParCles(int $idCompteRendu, int $idRapportEtudiant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', $colonnes);
        $sql = "SELECT {$listeColonnes} FROM {$this->table} WHERE id_compte_rendu = :id_compte_rendu AND id_rapport_etudiant = :id_rapport_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_INT);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_INT);
        $declaration->execute();
        $resultat = $declaration->fetch(PDO::FETCH_ASSOC);
        return $resultat ?: null;
    }

    public function supprimerLiaisonPvSessionRapportParCles(int $idCompteRendu, int $idRapportEtudiant): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_compte_rendu = :id_compte_rendu AND id_rapport_etudiant = :id_rapport_etudiant";
        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':id_compte_rendu', $idCompteRendu, PDO::PARAM_INT);
        $declaration->bindParam(':id_rapport_etudiant', $idRapportEtudiant, PDO::PARAM_INT);
        return $declaration->execute();
    }
}