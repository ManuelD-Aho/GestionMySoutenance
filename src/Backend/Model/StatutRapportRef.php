<?php
namespace App\Backend\Model;

use PDO;

class StatutRapportRef extends BaseModel
{
    protected string $table = 'statut_rapport_ref';
    protected string|array $primaryKey = 'id_statut_rapport';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}