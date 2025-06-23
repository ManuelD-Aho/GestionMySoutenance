<?php
namespace App\Backend\Model;

use PDO;

class SessionRapport extends BaseModel
{
    protected string $table = 'session_rapport';
    protected string|array $primaryKey = ['id_session', 'id_rapport_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}