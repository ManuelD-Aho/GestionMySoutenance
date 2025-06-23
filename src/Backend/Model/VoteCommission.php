<?php
namespace App\Backend\Model;

use PDO;

class VoteCommission extends BaseModel
{
    protected string $table = 'vote_commission';
    protected string|array $primaryKey = 'id_vote';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getSession(): ?array
    {
        if (!isset($this->id_session)) return null;
        $sessionModel = new SessionValidation($this->db);
        return $sessionModel->trouverParIdentifiant($this->id_session);
    }

    public function getRapport(): ?array
    {
        if (!isset($this->id_rapport_etudiant)) return null;
        $rapportModel = new RapportEtudiant($this->db);
        return $rapportModel->trouverParIdentifiant($this->id_rapport_etudiant);
    }

    public function getVotant(): ?array
    {
        if (!isset($this->numero_enseignant)) return null;
        $enseignantModel = new Enseignant($this->db);
        return $enseignantModel->trouverParIdentifiant($this->numero_enseignant);
    }

    public function getDecision(): ?array
    {
        if (!isset($this->id_decision_vote)) return null;
        $decisionModel = new DecisionVoteRef($this->db);
        return $decisionModel->trouverParIdentifiant($this->id_decision_vote);
    }
}