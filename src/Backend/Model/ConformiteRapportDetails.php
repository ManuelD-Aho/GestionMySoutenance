<?php
namespace App\Backend\Model;

use PDO;

class ConformiteRapportDetails extends BaseModel
{
    protected string $table = 'conformite_rapport_details';
    protected string|array $primaryKey = 'id_conformite_detail';

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

    public function getCritere(): ?array
    {
        if (!isset($this->id_critere)) return null;
        $critereModel = new CritereConformiteRef($this->db);
        return $critereModel->trouverParIdentifiant($this->id_critere);
    }
}