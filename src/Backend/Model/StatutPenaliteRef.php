<?php
namespace App\Backend\Model;

use PDO;

class StatutPenaliteRef extends BaseModel
{
    protected string $table = 'statut_penalite_ref';
    protected string|array $primaryKey = 'id_statut_penalite';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}