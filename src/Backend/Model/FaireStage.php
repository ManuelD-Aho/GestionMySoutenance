<?php
namespace App\Backend\Model;

use PDO;

class FaireStage extends BaseModel
{
    public string $table = 'faire_stage';
    public string|array $primaryKey = ['id_entreprise', 'numero_carte_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un stage par ses clés.
     * @param string $idEntreprise
     * @param string $numeroCarteEtudiant
     * @return array|null
     */
    public function trouverStageParCles(string $idEntreprise, string $numeroCarteEtudiant): ?array
    {
        return $this->trouverUnParCritere([
            'id_entreprise' => $idEntreprise,
            'numero_carte_etudiant' => $numeroCarteEtudiant
        ]);
    }
}