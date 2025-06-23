<?php
namespace App\Backend\Model;

use PDO;

class NiveauEtude extends BaseModel
{
    protected string $table = 'niveau_etude';
    protected string|array $primaryKey = 'id_niveau_etude';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}