<?php
namespace App\Backend\Model;

use PDO;

class Enregistrer extends BaseModel
{
    protected string $table = 'enregistrer';
    protected string|array $primaryKey = 'id_enregistrement';

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

    public function getAction(): ?array
    {
        if (!isset($this->id_action)) return null;
        $actionModel = new Action($this->db);
        return $actionModel->trouverParIdentifiant($this->id_action);
    }

    public function getDetailsActionAsArray(): ?array
    {
        return isset($this->details_action) ? json_decode($this->details_action, true) : null;
    }
}