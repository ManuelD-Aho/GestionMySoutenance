<?php
namespace App\Backend\Model;

use PDO;

class RapportEtudiant extends BaseModel
{
    protected string $table = 'rapport_etudiant';
    protected string|array $primaryKey = 'id_rapport_etudiant';

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

    public function getStatut(): ?array
    {
        if (!isset($this->id_statut_rapport)) return null;
        $statutModel = new StatutRapportRef($this->db);
        return $statutModel->trouverParIdentifiant($this->id_statut_rapport);
    }

    public function getSections(): array
    {
        if (!isset($this->id_rapport_etudiant)) return [];
        $sectionModel = new SectionRapport($this->db);
        return $sectionModel->trouverParCritere(['id_rapport_etudiant' => $this->id_rapport_etudiant], ['*'], 'AND', 'ordre ASC');
    }

    public function getVotes(): array
    {
        if (!isset($this->id_rapport_etudiant)) return [];
        $voteModel = new VoteCommission($this->db);
        return $voteModel->trouverParCritere(['id_rapport_etudiant' => $this->id_rapport_etudiant], ['*'], 'AND', 'date_vote DESC');
    }

    public function getDirecteurMemoire(): ?array
    {
        if (!isset($this->id_rapport_etudiant)) return null;
        $affecterModel = new Affecter($this->db);
        $affectation = $affecterModel->trouverUnParCritere([
            'id_rapport_etudiant' => $this->id_rapport_etudiant,
            'directeur_memoire' => 1
        ]);
        if (!$affectation) return null;

        $enseignantModel = new Enseignant($this->db);
        return $enseignantModel->trouverParIdentifiant($affectation['numero_enseignant']);
    }

    public function isBrouillon(): bool
    {
        return isset($this->id_statut_rapport) && $this->id_statut_rapport === 'RAP_BROUILLON';
    }

    public function isSoumis(): bool
    {
        return isset($this->id_statut_rapport) && $this->id_statut_rapport === 'RAP_SOUMIS';
    }

    public function isEnCorrection(): bool
    {
        return isset($this->id_statut_rapport) && in_array($this->id_statut_rapport, ['RAP_NON_CONF', 'RAP_CORRECT']);
    }

    public function isValide(): bool
    {
        return isset($this->id_statut_rapport) && $this->id_statut_rapport === 'RAP_VALID';
    }

    public function estVerrouillePourEtudiant(): bool
    {
        return !$this->isBrouillon() && !$this->isEnCorrection();
    }
}