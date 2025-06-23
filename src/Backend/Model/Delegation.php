<?php
namespace App\Backend\Model;

use PDO;

class Delegation extends BaseModel
{
    protected string $table = 'delegation';
    protected string|array $primaryKey = 'id_delegation';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getDelegant(): ?array
    {
        if (!isset($this->id_delegant)) return null;
        $userModel = new Utilisateur($this->db);
        return $userModel->trouverParIdentifiant($this->id_delegant);
    }

    public function getDelegue(): ?array
    {
        if (!isset($this->id_delegue)) return null;
        $userModel = new Utilisateur($this->db);
        return $userModel->trouverParIdentifiant($this->id_delegue);
    }

    public function getTraitement(): ?array
    {
        if (!isset($this->id_traitement)) return null;
        $traitementModel = new Traitement($this->db);
        return $traitementModel->trouverParIdentifiant($this->id_traitement);
    }

    public function isEnCours(): bool
    {
        if (!isset($this->date_debut) || !isset($this->date_fin) || !isset($this->statut)) return false;
        $now = new \DateTime();
        $start = new \DateTime($this->date_debut);
        $end = new \DateTime($this->date_fin);
        return $this->statut === 'Active' && $now >= $start && $now <= $end;
    }
}