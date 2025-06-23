<?php
namespace App\Backend\Model;

use PDO;

class Approuver extends BaseModel
{
    protected string $table = 'approuver';
    protected string|array $primaryKey = ['numero_personnel_administratif', 'id_rapport_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}