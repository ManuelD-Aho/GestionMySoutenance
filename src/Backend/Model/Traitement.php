<?php
namespace App\Backend\Model;

use PDO;

class Traitement extends BaseModel
{
    public string $table = 'traitement';
    public string|array $primaryKey = 'id_traitement';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}