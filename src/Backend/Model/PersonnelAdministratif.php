<?php
namespace App\Backend\Model;

use PDO;

class PersonnelAdministratif extends BaseModel
{
    public string $table = 'personnel_administratif';
    public string|array $primaryKey = 'numero_personnel_administratif';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}