<?php
namespace App\Backend\Model;

use PDO;

class PvSessionRapport extends BaseModel
{
    public string $table = 'pv_session_rapport';
    public string|array $primaryKey = ['id_compte_rendu', 'id_rapport_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}