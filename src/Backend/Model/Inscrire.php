<?php
namespace App\Backend\Model;

use PDO;

class Inscrire extends BaseModel
{
    protected string $table = 'inscrire';
    protected string|array $primaryKey = ['numero_carte_etudiant', 'id_niveau_etude', 'id_annee_academique'];

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

    public function getNiveauEtude(): ?array
    {
        if (!isset($this->id_niveau_etude)) return null;
        $niveauModel = new NiveauEtude($this->db);
        return $niveauModel->trouverParIdentifiant($this->id_niveau_etude);
    }

    public function getAnneeAcademique(): ?array
    {
        if (!isset($this->id_annee_academique)) return null;
        $anneeModel = new AnneeAcademique($this->db);
        return $anneeModel->trouverParIdentifiant($this->id_annee_academique);
    }

    public function getStatutPaiement(): ?array
    {
        if (!isset($this->id_statut_paiement)) return null;
        $statutModel = new StatutPaiementRef($this->db);
        return $statutModel->trouverParIdentifiant($this->id_statut_paiement);
    }

    public function isPaye(): bool
    {
        return isset($this->id_statut_paiement) && $this->id_statut_paiement === 'PAIE_OK';
    }
}