<?php
namespace App\Backend\Model;

use PDO;

class AnneeAcademique extends BaseModel
{
    protected string $table = 'annee_academique';
    protected string|array $primaryKey = 'id_annee_academique';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function isEstActive(): bool
    {
        return isset($this->est_active) ? (bool) $this->est_active : false;
    }

    public function getInscriptions(): array
    {
        if (!isset($this->id_annee_academique)) return [];
        $inscrireModel = new Inscrire($this->db);
        return $inscrireModel->trouverParCritere(['id_annee_academique' => $this->id_annee_academique]);
    }
}