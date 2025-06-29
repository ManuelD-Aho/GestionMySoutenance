<?php
namespace App\Backend\Model;

use PDO;

class Approuver extends BaseModel
{
    public string $table = 'approuver';
    public string|array $primaryKey = ['numero_personnel_administratif', 'id_rapport_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une approbation par ses clés.
     * @param string $numeroPersonnelAdministratif
     * @param string $idRapportEtudiant
     * @return array|null
     */
    public function trouverApprobationParCles(string $numeroPersonnelAdministratif, string $idRapportEtudiant): ?array
    {
        return $this->trouverUnParCritere([
            'numero_personnel_administratif' => $numeroPersonnelAdministratif,
            'id_rapport_etudiant' => $idRapportEtudiant
        ]);
    }
}