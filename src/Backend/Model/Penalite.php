<?php
namespace App\Backend\Model;

use PDO;

class Penalite extends BaseModel
{
    protected string $table = 'penalite';
    protected string|array $primaryKey = 'id_penalite';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getEtudiant(): ?array
    {
        if (!isset($this->numero_carte_etudiant)) return null;
        $etudiantModel = new Etudiant($this->db);
        return $etudiantModel->trouverParIdentifiant($this->numero_carte_etudiant);
    }

    public function getStatutPenalite(): ?array
    {
        if (!isset($this->id_statut_penalite)) return null;
        $statutModel = new StatutPenaliteRef($this->db);
        return $statutModel->trouverParIdentifiant($this->id_statut_penalite);
    }

    public function getPersonnelTraitant(): ?array
    {
        if (!isset($this->numero_personnel_traitant)) return null;
        $personnelModel = new PersonnelAdministratif($this->db);
        return $personnelModel->trouverParIdentifiant($this->numero_personnel_traitant);
    }

    public function isDue(): bool
    {
        return isset($this->id_statut_penalite) && $this->id_statut_penalite === 'PEN_DUE';
    }
}