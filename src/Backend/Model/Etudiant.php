<?php
namespace App\Backend\Model;

use PDO;

class Etudiant extends BaseModel
{
    protected string $table = 'etudiant';
    protected string|array $primaryKey = 'numero_carte_etudiant';

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

    public function getInscriptions(): array
    {
        if (!isset($this->numero_carte_etudiant)) return [];
        $inscrireModel = new Inscrire($this->db);
        return $inscrireModel->trouverParCritere(['numero_carte_etudiant' => $this->numero_carte_etudiant], ['*'], 'AND', 'id_annee_academique DESC');
    }

    public function getInscriptionActuelle(): ?array
    {
        if (!isset($this->numero_carte_etudiant)) return null;
        $anneeModel = new AnneeAcademique($this->db);
        $anneeActive = $anneeModel->trouverUnParCritere(['est_active' => 1]);
        if (!$anneeActive) return null;

        $inscrireModel = new Inscrire($this->db);
        $inscriptions = $inscrireModel->trouverParCritere([
            'numero_carte_etudiant' => $this->numero_carte_etudiant,
            'id_annee_academique' => $anneeActive['id_annee_academique']
        ]);
        return $inscriptions[0] ?? null;
    }

    public function getRapports(): array
    {
        if (!isset($this->numero_carte_etudiant)) return [];
        $rapportModel = new RapportEtudiant($this->db);
        return $rapportModel->trouverParCritere(['numero_carte_etudiant' => $this->numero_carte_etudiant]);
    }

    public function getPenalites(): array
    {
        if (!isset($this->numero_carte_etudiant)) return [];
        $penaliteModel = new Penalite($this->db);
        return $penaliteModel->trouverParCritere(['numero_carte_etudiant' => $this->numero_carte_etudiant]);
    }

    public function aPenaliteEnCours(): bool
    {
        if (!isset($this->numero_carte_etudiant)) return false;
        $penaliteModel = new Penalite($this->db);
        return $penaliteModel->compterParCritere([
                'numero_carte_etudiant' => $this->numero_carte_etudiant,
                'id_statut_penalite' => 'PEN_DUE'
            ]) > 0;
    }
}