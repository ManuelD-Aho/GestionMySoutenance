<?php
namespace App\Backend\Model;

use PDO;

class CompteRendu extends BaseModel
{
    protected string $table = 'compte_rendu';
    protected string|array $primaryKey = 'id_compte_rendu';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getRedacteur(): ?array
    {
        if (!isset($this->id_redacteur)) return null;
        $userModel = new Utilisateur($this->db);
        return $userModel->trouverParIdentifiant($this->id_redacteur);
    }

    public function getStatutPv(): ?array
    {
        if (!isset($this->id_statut_pv)) return null;
        $statutModel = new StatutPvRef($this->db);
        return $statutModel->trouverParIdentifiant($this->id_statut_pv);
    }

    public function getRapportEtudiant(): ?array
    {
        if (!isset($this->id_rapport_etudiant)) return null;
        $rapportModel = new RapportEtudiant($this->db);
        return $rapportModel->trouverParIdentifiant($this->id_rapport_etudiant);
    }

    public function getRapportsDeSession(): array
    {
        if (!isset($this->type_pv) || $this->type_pv !== 'Session' || !isset($this->id_compte_rendu)) return [];
        $pvSessionRapportModel = new PvSessionRapport($this->db);
        return $pvSessionRapportModel->trouverParCritere(['id_compte_rendu' => $this->id_compte_rendu]);
    }

    public function getValidations(): array
    {
        if (!isset($this->id_compte_rendu)) return [];
        $validationModel = new ValidationPv($this->db);
        return $validationModel->trouverParCritere(['id_compte_rendu' => $this->id_compte_rendu]);
    }
}