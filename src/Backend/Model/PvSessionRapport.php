<?php
namespace App\Backend\Model;

use PDO;

class PvSessionRapport extends BaseModel
{
    protected string $table = 'pv_session_rapport';
    protected string|array $primaryKey = ['id_compte_rendu', 'id_rapport_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}