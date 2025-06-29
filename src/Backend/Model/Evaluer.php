<?php
namespace App\Backend\Model;

use PDO;

class Evaluer extends BaseModel
{
    public string $table = 'evaluer';
    public string|array $primaryKey = ['numero_carte_etudiant', 'id_ecue', 'id_annee_academique'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une évaluation par ses clés.
     * @param string $numeroCarteEtudiant
     * @param string $idEcue
     * @param string $idAnneeAcademique
     * @return array|null
     */
    public function trouverEvaluationParCles(string $numeroCarteEtudiant, string $idEcue, string $idAnneeAcademique): ?array
    {
        return $this->trouverUnParCritere([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_ecue' => $idEcue,
            'id_annee_academique' => $idAnneeAcademique
        ]);
    }
}