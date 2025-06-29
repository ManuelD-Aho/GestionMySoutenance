<?php
namespace App\Backend\Model;

use PDO;

class NiveauEtude extends BaseModel
{
    public string $table = 'niveau_etude';
    public string|array $primaryKey = 'id_niveau_etude';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}