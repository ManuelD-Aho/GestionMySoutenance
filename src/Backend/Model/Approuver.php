<?php
namespace App\Backend\Model;

use PDO;

class Approuver extends BaseModel
{
    protected string $table = 'approuver';
    protected string|array $primaryKey = ['numero_personnel_administratif', 'id_rapport_etudiant']; // Clé composite

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une approbation spécifique par ses clés composées.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel administratif.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'approbation ou null si non trouvée.
     */
    public function trouverApprobationParCles(string $numeroPersonnelAdministratif, string $idRapportEtudiant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_personnel_administratif' => $numeroPersonnelAdministratif,
            'id_rapport_etudiant' => $idRapportEtudiant
        ], $colonnes);
    }

    /**
     * Met à jour une approbation spécifique par ses clés composées.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel administratif.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourApprobationParCles(string $numeroPersonnelAdministratif, string $idRapportEtudiant, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes(['numero_personnel_administratif' => $numeroPersonnelAdministratif, 'id_rapport_etudiant' => $idRapportEtudiant], $donnees);
    }

    /**
     * Supprime une approbation spécifique par ses clés composées.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel administratif.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerApprobationParCles(string $numeroPersonnelAdministratif, string $idRapportEtudiant): bool
    {
        return $this->supprimerParClesInternes(['numero_personnel_administratif' => $numeroPersonnelAdministratif, 'id_rapport_etudiant' => $idRapportEtudiant]);
    }
}