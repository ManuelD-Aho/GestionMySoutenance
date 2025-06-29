<?php
namespace App\Backend\Model;

use PDO;

class ConformiteRapportDetails extends BaseModel
{
    public string $table = 'conformite_rapport_details';
    public string|array $primaryKey = 'id_conformite_detail';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}