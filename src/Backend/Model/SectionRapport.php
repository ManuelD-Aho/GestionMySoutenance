<?php
namespace App\Backend\Model;

use PDO;

class SectionRapport extends BaseModel
{
    public string $table = 'section_rapport';
    public string|array $primaryKey = ['id_rapport_etudiant', 'titre_section'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve toutes les sections d'un rapport donné, ordonnées.
     * @param string $idRapportEtudiant
     * @return array
     */
    public function trouverSectionsPourRapport(string $idRapportEtudiant): array
    {
        return $this->trouverParCritere(
            ['id_rapport_etudiant' => $idRapportEtudiant],
            ['*'],
            'AND',
            'ordre ASC'
        );
    }
}