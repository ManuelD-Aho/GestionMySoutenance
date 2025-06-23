<?php
namespace App\Backend\Model;

use PDO;

class Sessions extends BaseModel
{
    protected string $table = 'sessions';
    protected string|array $primaryKey = 'session_id';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getUtilisateur(): ?array
    {
        if (!isset($this->user_id)) return null;
        $userModel = new Utilisateur($this->db);
        return $userModel->trouverParIdentifiant($this->user_id);
    }
}