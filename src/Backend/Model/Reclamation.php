<?php
namespace App\Backend\Model;

use PDO;

class Reclamation extends BaseModel
{
    public string $table = 'reclamation';
    public string|array $primaryKey = 'id_reclamation';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}