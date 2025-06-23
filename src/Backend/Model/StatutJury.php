<?php
namespace App\Backend\Model;

use PDO;

class StatutJury extends BaseModel
{
    protected string $table = 'statut_jury';
    protected string|array $primaryKey = 'id_statut_jury';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}