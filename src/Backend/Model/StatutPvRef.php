<?php
namespace App\Backend\Model;

use PDO;

class StatutPvRef extends BaseModel
{
    protected string $table = 'statut_pv_ref';
    protected string|array $primaryKey = 'id_statut_pv';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}