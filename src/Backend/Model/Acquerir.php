<?php
namespace App\Backend\Model;

use PDO;

class Acquerir extends BaseModel
{
    public string $table = 'acquerir';
    public string|array $primaryKey = ['id_grade', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une acquisition spécifique par ses clés composées.
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @return array|null Les données de l'acquisition ou null si non trouvée.
     */
    public function trouverAcquisitionParCles(string $idGrade, string $numeroEnseignant): ?array
    {
        return $this->trouverUnParCritere([
            'id_grade' => $idGrade,
            'numero_enseignant' => $numeroEnseignant
        ]);
    }

    /**
     * Met à jour une acquisition spécifique par ses clés composées.
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param array $donnees Les données à mettre à jour.
     * @return bool True en cas de succès, false sinon.
     */
    public function mettreAJourAcquisitionParCles(string $idGrade, string $numeroEnseignant, array $donnees): bool
    {
        return $this->mettreAJourParCles(['id_grade' => $idGrade, 'numero_enseignant' => $numeroEnseignant], $donnees);
    }

    /**
     * Supprime une acquisition spécifique par ses clés composées.
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @return bool True en cas de succès, false sinon.
     */
    public function supprimerAcquisitionParCles(string $idGrade, string $numeroEnseignant): bool
    {
        return $this->supprimerParCles(['id_grade' => $idGrade, 'numero_enseignant' => $numeroEnseignant]);
    }
}