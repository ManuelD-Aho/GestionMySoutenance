<?php
namespace App\Backend\Model;

use PDO;

class Acquerir extends BaseModel
{
    protected string $table = 'acquerir';
    protected string|array $primaryKey = ['id_grade', 'numero_enseignant']; // Clé composite

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une acquisition spécifique par ses clés composées.
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'acquisition ou null si non trouvée.
     */
    public function trouverAcquisitionParCles(string $idGrade, string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_grade' => $idGrade,
            'numero_enseignant' => $numeroEnseignant
        ], $colonnes);
    }

    /**
     * Met à jour une acquisition spécifique par ses clés composées.
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourAcquisitionParCles(string $idGrade, string $numeroEnseignant, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes(['id_grade' => $idGrade, 'numero_enseignant' => $numeroEnseignant], $donnees);
    }

    /**
     * Supprime une acquisition spécifique par ses clés composées.
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerAcquisitionParCles(string $idGrade, string $numeroEnseignant): bool
    {
        return $this->supprimerParClesInternes(['id_grade' => $idGrade, 'numero_enseignant' => $numeroEnseignant]);
    }
}