<?php
namespace App\Backend\Model;

use PDO;

class PersonnelAdministratif extends BaseModel
{
    protected string $table = 'personnel_administratif';
    protected string|array $primaryKey = 'numero_personnel_administratif';

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
}