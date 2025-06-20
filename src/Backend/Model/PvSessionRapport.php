<?php
namespace App\Backend\Model;

use PDO;

class PvSessionRapport extends BaseModel
{
    protected string $table = 'pv_session_rapport';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['id_compte_rendu', 'id_rapport_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une liaison entre un PV de session et un rapport spécifique par ses clés composées.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de la liaison ou null si non trouvée.
     */
    public function trouverLiaisonPvSessionRapportParCles(string $idCompteRendu, string $idRapportEtudiant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_compte_rendu' => $idCompteRendu,
            'id_rapport_etudiant' => $idRapportEtudiant
        ], $colonnes);
    }

    /**
     * Supprime une liaison entre un PV de session et un rapport spécifique par ses clés composées.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerLiaisonPvSessionRapportParCles(string $idCompteRendu, string $idRapportEtudiant): bool
    {
        return $this->supprimerParClesInternes([
            'id_compte_rendu' => $idCompteRendu,
            'id_rapport_etudiant' => $idRapportEtudiant
        ]);
    }
    // La méthode mettreAJourLiaisonPvSessionRapportParCles n'était pas présente mais peut être ajoutée si nécessaire.
    // public function mettreAJourLiaisonPvSessionRapportParCles(string $idCompteRendu, string $idRapportEtudiant, array $donnees): bool
    // {
    //     return $this->mettreAJourParClesInternes(['id_compte_rendu' => $idCompteRendu, 'id_rapport_etudiant' => $idRapportEtudiant], $donnees);
    // }
}