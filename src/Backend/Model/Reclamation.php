<?php
namespace App\Backend\Model;

use PDO;

class Reclamation extends BaseModel
{
    protected string $table = 'reclamation';
    protected string|array $primaryKey = 'id_reclamation';

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

    public function getStatutReclamation(): ?array
    {
        if (!isset($this->id_statut_reclamation)) return null;
        $statutModel = new StatutReclamationRef($this->db);
        return $statutModel->trouverParIdentifiant($this->id_statut_reclamation);
    }

    public function getPersonnelTraitant(): ?array
    {
        if (!isset($this->numero_personnel_traitant)) return null;
        $personnelModel = new PersonnelAdministratif($this->db);
        return $personnelModel->trouverParIdentifiant($this->numero_personnel_traitant);
    }
}