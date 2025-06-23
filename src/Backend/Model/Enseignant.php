<?php
namespace App\Backend\Model;

use PDO;

class Enseignant extends BaseModel
{
    protected string $table = 'enseignant';
    protected string|array $primaryKey = 'numero_enseignant';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getUtilisateur(): ?array
    {
        if (!isset($this->numero_utilisateur)) return null;
        $userModel = new Utilisateur($this->db);
        return $userModel->trouverParIdentifiant($this->numero_utilisateur);
    }

    public function getNomComplet(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }

    public function getGradeActuel(): ?array
    {
        if (!isset($this->numero_enseignant)) return null;
        $acquerirModel = new Acquerir($this->db);
        return $acquerirModel->trouverUnParCritere(['numero_enseignant' => $this->numero_enseignant], ['*'], 'AND', 'date_acquisition DESC');
    }

    public function getGradesHistorique(): array
    {
        if (!isset($this->numero_enseignant)) return [];
        $acquerirModel = new Acquerir($this->db);
        return $acquerirModel->trouverParCritere(['numero_enseignant' => $this->numero_enseignant], ['*'], 'AND', 'date_acquisition DESC');
    }

    public function getFonctionsHistorique(): array
    {
        if (!isset($this->numero_enseignant)) return [];
        $occuperModel = new Occuper($this->db);
        return $occuperModel->trouverParCritere(['numero_enseignant' => $this->numero_enseignant], ['*'], 'AND', 'date_debut_occupation DESC');
    }

    public function getSpecialites(): array
    {
        if (!isset($this->numero_enseignant)) return [];
        $attribuerModel = new Attribuer($this->db);
        return $attribuerModel->trouverParCritere(['numero_enseignant' => $this->numero_enseignant]);
    }
}