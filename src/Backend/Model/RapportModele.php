<?php
namespace App\Backend\Model;

use PDO;

class RapportModele extends BaseModel
{
    protected string $table = 'rapport_modele';
    protected string|array $primaryKey = 'id_modele';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getSectionsModele(): array
    {
        if (!isset($this->id_modele)) return [];
        $sectionModel = new RapportModeleSection($this->db);
        return $sectionModel->trouverParCritere(['id_modele' => $this->id_modele], ['*'], 'AND', 'ordre ASC');
    }

    public function getNiveauxEtudeAssignes(): array
    {
        if (!isset($this->id_modele)) return [];
        $assignationModel = new RapportModeleAssignation($this->db);
        return $assignationModel->trouverParCritere(['id_modele' => $this->id_modele]);
    }

    public function publier(): bool
    {
        return $this->mettreAJourParIdentifiant($this->id_modele, ['statut' => 'Publié']);
    }

    public function archiver(): bool
    {
        return $this->mettreAJourParIdentifiant($this->id_modele, ['statut' => 'Archivé']);
    }
}