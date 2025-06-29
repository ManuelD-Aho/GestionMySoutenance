<?php
namespace App\Backend\Model;

use PDO;

class Penalite extends BaseModel
{
    public string $table = 'penalite';
    public string|array $primaryKey = 'id_penalite';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve les pénalités non réglées d'un étudiant.
     * @param string $numeroCarteEtudiant
     * @return array
     */
    public function trouverPenalitesNonRegul(string $numeroCarteEtudiant): array
    {
        return $this->trouverParCritere([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_statut_penalite' => 'PEN_DUE' // 'DUE' est la valeur pour les pénalités dues
        ]);
    }
}