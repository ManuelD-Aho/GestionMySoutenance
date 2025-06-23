<?php
namespace App\Backend\Model;

use PDO;

class SectionRapport extends BaseModel
{
    protected string $table = 'section_rapport';
    protected string|array $primaryKey = 'id_section';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getRapportEtudiant(): ?array
    {
        if (!isset($this->id_rapport_etudiant)) return null;
        $rapportModel = new RapportEtudiant($this->db);
        return $rapportModel->trouverParIdentifiant($this->id_rapport_etudiant);
    }
}