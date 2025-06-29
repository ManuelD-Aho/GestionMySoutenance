<?php
namespace App\Backend\Model;

use PDO;

class Affecter extends BaseModel
{
    public string $table = 'affecter';
    public string|array $primaryKey = ['numero_enseignant', 'id_rapport_etudiant', 'id_statut_jury'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une affectation par ses clés.
     * @param string $numeroEnseignant
     * @param string $idRapportEtudiant
     * @param string $idStatutJury
     * @return array|null
     */
    public function trouverAffectationParCles(string $numeroEnseignant, string $idRapportEtudiant, string $idStatutJury): ?array
    {
        return $this->trouverUnParCritere([
            'numero_enseignant' => $numeroEnseignant,
            'id_rapport_etudiant' => $idRapportEtudiant,
            'id_statut_jury' => $idStatutJury
        ]);
    }
}