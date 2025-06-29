<?php
namespace App\Backend\Model;

use PDO;

class Inscrire extends BaseModel
{
    public string $table = 'inscrire';
    public string|array $primaryKey = ['numero_carte_etudiant', 'id_niveau_etude', 'id_annee_academique'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une inscription par ses clés.
     * @param string $numeroCarteEtudiant
     * @param string $idNiveauEtude
     * @param string $idAnneeAcademique
     * @return array|null
     */
    public function trouverInscriptionParCles(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): ?array
    {
        return $this->trouverUnParCritere([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_niveau_etude' => $idNiveauEtude,
            'id_annee_academique' => $idAnneeAcademique
        ]);
    }
}